<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    /**
     * Generate secure, sequential, atomic document number with pessimistic locking.
     * Prevents race conditions during simultaneous user submissions.
     *
     * Format: {TYPE}/{SCOPE}/{YEAR}/{MONTH}/{XXXX}
     * Example: PR/IT/2026/09/0001 or PO/PROC/2026/09/0042
     */
    public static function generate(string $type, string $scopeCode = 'GEN', ?int $year = null, ?int $month = null): string
    {
        $type = strtoupper(trim($type));
        $scopeCode = strtoupper(preg_replace('/[^A-Z0-9]/i', '', trim($scopeCode)));
        if (empty($scopeCode)) {
            $scopeCode = 'GEN';
        }

        $year = $year ?? (int) date('Y');
        $month = $month ?? (int) date('n');

        $nextNumber = DB::transaction(function () use ($type, $scopeCode, $year, $month) {
            // Lock the sequence row exclusively to prevent race conditions
            $sequence = DB::table('document_sequences')
                ->where('document_type', $type)
                ->where('scope_code', $scopeCode)
                ->where('period_year', $year)
                ->where('period_month', $month)
                ->lockForUpdate()
                ->first();

            if (!$sequence) {
                // If sequence doesn't exist for this month/year/scope, initialize at 1
                DB::table('document_sequences')->insert([
                    'document_type' => $type,
                    'scope_code' => $scopeCode,
                    'period_year' => $year,
                    'period_month' => $month,
                    'current_number' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return 1;
            }

            $next = $sequence->current_number + 1;

            DB::table('document_sequences')
                ->where('id', $sequence->id)
                ->update([
                    'current_number' => $next,
                    'updated_at' => now(),
                ]);

            return $next;
        });

        $monthPadded = str_pad((string) $month, 2, '0', STR_PAD_LEFT);
        $numberPadded = str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);

        return "{$type}/{$scopeCode}/{$year}/{$monthPadded}/{$numberPadded}";
    }

    /**
     * Generate Enterprise Purchase Request number
     * e.g. PR/IT/2026/09/0001
     */
    public static function generatePrNumber($department = null): string
    {
        $deptCode = 'GEN';

        if ($department instanceof Department) {
            $deptCode = $department->code ?: 'GEN';
        } elseif (is_numeric($department)) {
            $dept = Department::find($department);
            $deptCode = $dept?->code ?: 'GEN';
        } elseif (is_string($department) && !empty($department)) {
            $deptCode = $department;
        }

        return self::generate('PR', $deptCode);
    }

    /**
     * Generate Enterprise Purchase Order number
     * e.g. PO/PROC/2026/09/0001
     */
    public static function generatePoNumber(string $scope = 'PROC'): string
    {
        return self::generate('PO', $scope);
    }

    /**
     * Generate Enterprise Goods Receipt / BAST number
     * e.g. GR/WH/2026/09/0001 or BAST/IT/2026/09/0001
     */
    public static function generateGrNumber(string $type = 'goods', string $scope = 'WH'): string
    {
        $docType = ($type === 'service') ? 'BAST' : 'GR';
        return self::generate($docType, $scope);
    }

    /**
     * Generate Enterprise Internal Vendor Invoice registration number
     * e.g. INV/FIN/2026/09/0001
     */
    public static function generateInvoiceNumber(string $scope = 'FIN'): string
    {
        return self::generate('INV', $scope);
    }
}
