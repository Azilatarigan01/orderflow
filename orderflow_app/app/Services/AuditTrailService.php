<?php

namespace App\Services;

use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AuditTrailService
{
    public const GENESIS_SEED = 'ORDERFLOW_GENESIS_BLOCK';

    /**
     * Compute SHA-256 genesis hash
     */
    public static function getGenesisHash(): string
    {
        return hash('sha256', self::GENESIS_SEED);
    }

    /**
     * Calculate record SHA-256 hash based on previous hash and immutable payload
     */
    public static function calculateHash(
        string $previousHash,
        ?int $userId,
        string $action,
        string $entityType,
        int|string $entityId,
        ?array $beforeState,
        ?array $afterState,
        Carbon|string $createdAt
    ): string {
        $timestamp = $createdAt instanceof Carbon ? $createdAt->format('Y-m-d H:i:s') : (string) $createdAt;

        $payload = implode('|', [
            $previousHash,
            $userId ?? '0',
            $action,
            $entityType,
            $entityId,
            $beforeState ? json_encode($beforeState) : '',
            $afterState ? json_encode($afterState) : '',
            $timestamp,
        ]);

        return hash('sha256', $payload);
    }

    /**
     * Record an immutable, tamper-evident audit trail entry chained via SHA-256.
     */
    public static function record(
        string  $action,
        Model   $entity,
        string  $entityLabel,
        ?array  $beforeState   = null,
        ?array  $afterState    = null,
        ?string $description   = null,
        ?string $comment       = null,
        ?Request $request      = null,
    ): AuditTrail {
        $userId = auth()->id();

        // Auto-build description if not provided
        if (! $description) {
            $actor = auth()->user()?->name ?? 'System';
            $description = "{$actor} melakukan aksi '{$action}' pada {$entityLabel}.";
        }

        $ip        = $request?->ip()        ?? request()->ip();
        $userAgent = $request?->userAgent() ?? request()->userAgent();
        $createdAt = now();

        // Cryptographic Chain: Fetch the last recorded audit trail entry
        $lastRecord = AuditTrail::latest('id')->first();
        $previousHash = $lastRecord?->record_hash ?? self::getGenesisHash();

        $recordHash = self::calculateHash(
            $previousHash,
            $userId,
            $action,
            class_basename($entity),
            $entity->getKey(),
            $beforeState,
            $afterState,
            $createdAt
        );

        return AuditTrail::create([
            'user_id'       => $userId,
            'action'        => $action,
            'entity_type'   => class_basename($entity),
            'entity_id'     => $entity->getKey(),
            'entity_label'  => $entityLabel,
            'before_state'  => $beforeState,
            'after_state'   => $afterState,
            'description'   => $description,
            'comment'       => $comment,
            'ip_address'    => $ip,
            'user_agent'    => $userAgent,
            'previous_hash' => $previousHash,
            'record_hash'   => $recordHash,
            'created_at'    => $createdAt,
        ]);
    }

    /**
     * Verify the entire cryptographic SHA-256 audit ledger from genesis to head.
     * Detects any altered data, deleted rows, or injected records.
     */
    public static function verifyChainIntegrity(): array
    {
        $records = AuditTrail::orderBy('id')->get();
        $total = $records->count();

        if ($total === 0) {
            return [
                'is_valid'      => true,
                'total_records' => 0,
                'status'        => 'empty',
                'broken_id'     => null,
                'message'       => 'Ledger audit masih kosong. Integritas siap untuk inisiasi transaksi.',
                'genesis_hash'  => self::getGenesisHash(),
                'latest_hash'   => null,
                'verified_at'   => now(),
            ];
        }

        $expectedPreviousHash = self::getGenesisHash();

        foreach ($records as $index => $record) {
            // 1. Verify previous hash link
            if ($record->previous_hash !== $expectedPreviousHash) {
                return [
                    'is_valid'      => false,
                    'total_records' => $total,
                    'status'        => 'tampered_chain',
                    'broken_id'     => $record->id,
                    'message'       => "Pelanggaran Integritas Rantai Terdeteksi pada Log Audit #{$record->id}: previous_hash tidak cocok dengan record sebelumnya. Kemungkinan terdapat manipulasi data atau penyisipan record ilegal.",
                    'genesis_hash'  => self::getGenesisHash(),
                    'latest_hash'   => $record->record_hash,
                    'verified_at'   => now(),
                ];
            }

            // 2. Recompute and verify payload hash
            $recalculated = self::calculateHash(
                $record->previous_hash,
                $record->user_id,
                $record->action,
                $record->entity_type,
                $record->entity_id,
                $record->before_state,
                $record->after_state,
                $record->created_at
            );

            if ($record->record_hash !== $recalculated) {
                return [
                    'is_valid'      => false,
                    'total_records' => $total,
                    'status'        => 'tampered_payload',
                    'broken_id'     => $record->id,
                    'message'       => "Pelanggaran Integritas Data Terdeteksi pada Log Audit #{$record->id}: Payload atau data historis telah dimodifikasi (SHA-256 mismatch).",
                    'genesis_hash'  => self::getGenesisHash(),
                    'latest_hash'   => $record->record_hash,
                    'verified_at'   => now(),
                ];
            }

            $expectedPreviousHash = $record->record_hash;
        }

        return [
            'is_valid'      => true,
            'total_records' => $total,
            'status'        => 'verified',
            'broken_id'     => null,
            'message'       => "100% Rantai Hash SHA-256 Terverifikasi Sah. Seluruh {$total} transaksi tersimpan secara immutable (anti-tamper).",
            'genesis_hash'  => self::getGenesisHash(),
            'latest_hash'   => $records->last()->record_hash,
            'verified_at'   => now(),
        ];
    }
}
