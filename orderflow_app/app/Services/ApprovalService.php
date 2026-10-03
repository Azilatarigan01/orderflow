<?php

namespace App\Services;

use App\Models\PrApproval;
use App\Models\PurchaseRequest;
use App\Models\StatusHistory;
use App\Models\User;
use App\Models\ActingDelegation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ApprovalService
{
    /**
     * Generate sequential approval tiers based on PR total and department
     */
    public function generateApprovalTiers(PurchaseRequest $pr): void
    {
        // Delete any existing approvals (e.g. from previous draft/submission)
        $pr->approvals()->delete();

        // Tier 1: Department Manager (Always required)
        $pr->approvals()->create([
            'tier_level' => 1,
            'role_required' => 'manager',
            'department_id' => $pr->department_id,
            'status' => 'pending',
        ]);

        // Tier 2: Finance Team (Required if total > Rp 5,000,000)
        if ($pr->estimated_total > 5000000) {
            $pr->approvals()->create([
                'tier_level' => 2,
                'role_required' => 'finance',
                'department_id' => null,
                'status' => 'pending',
            ]);
        }

        // Tier 3: Direksi / Head of Department (Required if total > Rp 25,000,000)
        if ($pr->estimated_total > 25000000) {
            $pr->approvals()->create([
                'tier_level' => 3,
                'role_required' => 'hod',
                'department_id' => null,
                'status' => 'pending',
            ]);
        }

        // Notify Tier 1 approvers & active Plt delegates
        $this->notifyApproversForTier($pr, 1);
    }

    /**
     * Resolve if a user is acting on behalf of a delegator (Plt)
     */
    public function resolveActingDelegator(User $user, PrApproval $activeTier, PurchaseRequest $pr): ?User
    {
        // Admin acts directly with sovereign privileges
        if ($user->hasRole('admin')) {
            return null;
        }

        // Check if user qualifies directly without needing delegation
        if ($activeTier->role_required === 'manager' && $user->hasRole('manager') && $user->department_id === $pr->department_id) {
            return null;
        }

        if ($activeTier->role_required === 'finance' && $user->hasRole('finance')) {
            return null;
        }

        if ($activeTier->role_required === 'hod' && $user->hasRole('hod')) {
            return null;
        }

        // Resolve active Plt delegation
        $deptId = ($activeTier->role_required === 'manager') ? $pr->department_id : null;
        $delegation = $user->getActiveActingDelegationFor($activeTier->role_required, $deptId);

        return $delegation?->delegator;
    }

    /**
     * Check if a user is allowed to act on the current pending tier
     */
    public function canUserApprove(User $user, PurchaseRequest $pr): bool
    {
        if ($pr->status !== 'submitted') {
            return false;
        }

        // Account must be active
        if ($user->is_active === false) {
            return false;
        }

        // ANTI SELF-APPROVAL: Requester cannot approve their own PR
        if ($pr->user_id === $user->id) {
            return false;
        }

        $activeTier = $pr->currentPendingApproval();
        if (!$activeTier) {
            return false;
        }

        return $this->canUserApproveTier($user, $pr, $activeTier);
    }

    /**
     * Verify authority for a specific tier
     */
    public function canUserApproveTier(User $user, PurchaseRequest $pr, PrApproval $activeTier): bool
    {
        if ($user->is_active === false) {
            return false;
        }

        if ($pr->user_id === $user->id) {
            return false;
        }

        // Admin can approve any tier for emergency governance
        if ($user->hasRole('admin')) {
            return true;
        }

        // Explicitly assigned approver (via Admin re-assignment or handover)
        if ($activeTier->assigned_approver_id && $activeTier->assigned_approver_id === $user->id) {
            return true;
        }

        // Tier 1: Department Manager
        if ($activeTier->role_required === 'manager') {
            if ($user->hasRole('manager') && $user->department_id === $pr->department_id) {
                return true;
            }
            // Check Plt Acting delegation for this department's manager
            return $user->getActiveActingDelegationFor('manager', $pr->department_id) !== null;
        }

        // Tier 2: Finance
        if ($activeTier->role_required === 'finance') {
            if ($user->hasRole('finance')) {
                return true;
            }
            return $user->getActiveActingDelegationFor('finance') !== null;
        }

        // Tier 3: Direksi / HOD
        if ($activeTier->role_required === 'hod') {
            if ($user->hasRole('hod') || $user->hasRole('admin')) {
                return true;
            }
            return $user->getActiveActingDelegationFor('hod') !== null;
        }

        return false;
    }

    /**
     * Approve the current active tier with Plt delegation and SHA-256 audit logging
     */
    public function approve(User $user, PurchaseRequest $pr, ?string $notes = null): void
    {
        if ($pr->status !== 'submitted') {
            abort(409, "Pengajuan #{$pr->pr_number} telah diproses atau statusnya telah berubah beberapa saat yang lalu.");
        }

        if (!$pr->currentPendingApproval()) {
            abort(409, "Tahap persetujuan untuk PR #{$pr->pr_number} telah selesai diproses oleh pejabat lain.");
        }

        if (!$this->canUserApprove($user, $pr)) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk menyetujui tahap pengajuan ini atau tidak dapat menyetujui pengajuan milik sendiri.');
        }

        DB::transaction(function () use ($user, $pr, $notes) {
            // Pessimistic Locking: Lock PR row to serialize concurrent actions
            $lockedPr = PurchaseRequest::where('id', $pr->id)->lockForUpdate()->first();
            if (!$lockedPr || $lockedPr->status !== 'submitted') {
                abort(409, "Pengajuan #{$pr->pr_number} telah diproses atau statusnya telah berubah beberapa saat yang lalu.");
            }

            // Lock the active pending tier row
            $activeTier = $lockedPr->approvals()
                ->where('status', 'pending')
                ->orderBy('tier_level', 'asc')
                ->lockForUpdate()
                ->first();

            if (!$activeTier) {
                abort(409, "Tahap persetujuan untuk PR #{$pr->pr_number} telah selesai diproses oleh pejabat lain.");
            }

            if (!$this->canUserApproveTier($user, $lockedPr, $activeTier)) {
                abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang pada tahap persetujuan aktif saat ini.');
            }

            $delegator = $this->resolveActingDelegator($user, $activeTier, $lockedPr);
            $isActing = ($delegator !== null);

            $actingSuffix = $isActing ? " atas nama Plt {$delegator->name}" : "";
            $approvalNote = $notes ?: ($isActing ? "Disetujui oleh {$user->name} atas nama Plt {$delegator->name}." : 'Persetujuan disetujui sesuai otorisasi.');

            $activeTier->update([
                'status'             => 'approved',
                'approver_id'        => $user->id,
                'is_acting'          => $isActing,
                'acting_for_user_id' => $delegator?->id,
                'acted_at'           => now(),
                'notes'              => $approvalNote,
            ]);

            // Check if there is a next pending tier
            $nextTier = $lockedPr->approvals()
                ->where('status', 'pending')
                ->orderBy('tier_level', 'asc')
                ->lockForUpdate()
                ->first();

            if ($nextTier) {
                // PR remains submitted, waiting for next tier
                StatusHistory::create([
                    'purchase_request_id' => $lockedPr->id,
                    'from_status'         => 'submitted',
                    'to_status'           => 'submitted',
                    'user_id'             => $user->id,
                    'notes'               => "[{$activeTier->tier_label}] Disetujui oleh {$user->name}{$actingSuffix}. Menunggu {$nextTier->tier_label}.",
                ]);

                // Record SHA-256 Audit Trail
                AuditTrailService::record(
                    'pr_approved',
                    $lockedPr,
                    $lockedPr->pr_number,
                    beforeState: ['tier' => $activeTier->tier_level, 'status' => 'submitted'],
                    afterState: ['tier' => $nextTier->tier_level, 'status' => 'submitted'],
                    description: "Tahap [{$activeTier->tier_label}] disetujui oleh {$user->name}{$actingSuffix}."
                );

                // Multi-Channel Notification: notify next tier approvers
                $this->notifyApproversForTier($lockedPr, $nextTier->tier_level);
            } else {
                // Final approval reached!
                $lockedPr->update(['status' => 'approved']);

                StatusHistory::create([
                    'purchase_request_id' => $lockedPr->id,
                    'from_status'         => 'submitted',
                    'to_status'           => 'approved',
                    'user_id'             => $user->id,
                    'notes'               => "[{$activeTier->tier_label}] Persetujuan akhir tuntas oleh {$user->name}{$actingSuffix}. Pengadaan siap diproses tim Procurement.",
                ]);

                // Record SHA-256 Audit Trail
                AuditTrailService::record(
                    'pr_approved',
                    $lockedPr,
                    $lockedPr->pr_number,
                    beforeState: ['tier' => $activeTier->tier_level, 'status' => 'submitted'],
                    afterState: ['tier' => 'final', 'status' => 'approved'],
                    description: "Persetujuan final tuntas disahkan oleh {$user->name}{$actingSuffix}."
                );

                // Multi-Channel Notification: Notify Requester
                NotificationService::send(
                    $lockedPr->user,
                    "Pengajuan PR #{$lockedPr->pr_number} Telah Disetujui Penuh!",
                    "Pengajuan pembelian '{$lockedPr->title}' telah disetujui oleh seluruh pihak yang berwenang dan diteruskan ke Pengadaan.",
                    route('purchase-requests.show', $lockedPr),
                    'pr_approved'
                );

                // Multi-Channel Notification: Notify Procurement officers
                $procurementUsers = User::where('role', 'procurement')->get();
                foreach ($procurementUsers as $procUser) {
                    NotificationService::send(
                        $procUser,
                        "PR Baru Siap Diproses: #{$lockedPr->pr_number}",
                        "Pengajuan '{$lockedPr->title}' senilai Rp " . number_format($lockedPr->estimated_total, 0, ',', '.') . " telah disetujui penuh.",
                        route('purchase-requests.show', $lockedPr),
                        'pr_approved'
                    );
                }
            }
        });
    }

    /**
     * Request revision on the active tier
     */
    public function requestRevision(User $user, PurchaseRequest $pr, string $notes): void
    {
        if ($pr->status !== 'submitted') {
            abort(409, "Pengajuan #{$pr->pr_number} telah diproses atau statusnya telah berubah beberapa saat yang lalu.");
        }

        if (!$pr->currentPendingApproval()) {
            abort(409, "Tahap persetujuan untuk PR #{$pr->pr_number} telah selesai diproses oleh pejabat lain.");
        }

        if (!$this->canUserApprove($user, $pr)) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk meminta revisi pada pengajuan ini.');
        }

        DB::transaction(function () use ($user, $pr, $notes) {
            // Pessimistic Locking: Lock PR row to serialize concurrent actions
            $lockedPr = PurchaseRequest::where('id', $pr->id)->lockForUpdate()->first();
            if (!$lockedPr || $lockedPr->status !== 'submitted') {
                abort(409, "Pengajuan #{$pr->pr_number} telah diproses atau statusnya telah berubah beberapa saat yang lalu.");
            }

            // Lock the active pending tier row
            $activeTier = $lockedPr->approvals()
                ->where('status', 'pending')
                ->orderBy('tier_level', 'asc')
                ->lockForUpdate()
                ->first();

            if (!$activeTier) {
                abort(409, "Tahap persetujuan untuk PR #{$pr->pr_number} telah selesai diproses oleh pejabat lain.");
            }

            if (!$this->canUserApproveTier($user, $lockedPr, $activeTier)) {
                abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang pada tahap persetujuan aktif saat ini.');
            }

            $delegator = $this->resolveActingDelegator($user, $activeTier, $lockedPr);
            $isActing = ($delegator !== null);
            $actingSuffix = $isActing ? " atas nama Plt {$delegator->name}" : "";

            $activeTier->update([
                'status'             => 'revision_required',
                'approver_id'        => $user->id,
                'is_acting'          => $isActing,
                'acting_for_user_id' => $delegator?->id,
                'acted_at'           => now(),
                'notes'              => $notes,
            ]);

            $lockedPr->update(['status' => 'revision_required']);

            StatusHistory::create([
                'purchase_request_id' => $lockedPr->id,
                'from_status'         => 'submitted',
                'to_status'           => 'revision_required',
                'user_id'             => $user->id,
                'notes'               => "[{$activeTier->tier_label}] Permintaan revisi oleh {$user->name}{$actingSuffix}: {$notes}",
            ]);

            // SHA-256 Audit Trail
            AuditTrailService::record(
                'pr_revision',
                $lockedPr,
                $lockedPr->pr_number,
                beforeState: ['status' => 'submitted'],
                afterState: ['status' => 'revision_required'],
                description: "Revisi diminta oleh {$user->name}{$actingSuffix}. Catatan: {$notes}"
            );

            // Multi-Channel Notification: Notify Requester
            NotificationService::send(
                $lockedPr->user,
                "Permintaan Revisi PR #{$lockedPr->pr_number}",
                "Atasan ({$user->name}{$actingSuffix}) meminta perbaikan pada '{$lockedPr->title}': {$notes}",
                route('purchase-requests.edit', $lockedPr),
                'pr_revision'
            );
        });
    }

    /**
     * Handle intelligent resubmission routing after revision
     *
     * Rule Good Corporate Governance & Operasional ERP:
     * 1. Perubahan Material (Kenaikan Anggaran atau Perubahan Struktur Otorisasi):
     *    - Jika new_total > old_total atau batas otorisasi bergeser:
     *      Seluruh persetujuan sebelumnya di-reset ulang ke Tingkat 1 (Manager Divisi).
     * 2. Perubahan Non-Material (Klarifikasi Teknis / Dokumen Pendukung / Anggaran Tidak Naik):
     *    - Jika new_total <= old_total:
     *      Persetujuan tingkat sebelumnya TETAP SAH. Dokumen LANGSUNG KEMBALI ke meja approver yang meminta revisi.
     */
    public function handleResubmission(PurchaseRequest $pr, float $oldEstimatedTotal, User $resubmitter): array
    {
        $revisionTier = $pr->approvals()->where('status', 'revision_required')->first();

        // If no revision tier recorded, generate normally
        if (!$revisionTier) {
            $this->generateApprovalTiers($pr);
            return [
                'type' => 'fresh',
                'message' => 'Pengajuan berhasil diserahkan untuk peninjauan persetujuan berjenjang.',
            ];
        }

        $newTotal = (float) $pr->estimated_total;
        $isMaterialIncrease = ($newTotal > $oldEstimatedTotal);

        // Check if required tiers would change based on new total
        $requiredTiersCount = 1; // manager
        if ($newTotal > 5000000) $requiredTiersCount++;
        if ($newTotal > 25000000) $requiredTiersCount++;

        $existingTiersCount = $pr->approvals()->count();
        $isTierStructureChanged = ($requiredTiersCount !== $existingTiersCount);

        if ($isMaterialIncrease || $isTierStructureChanged) {
            // MATERIAL CHANGE -> FULL RESET TO TIER 1
            $this->generateApprovalTiers($pr);

            StatusHistory::create([
                'purchase_request_id' => $pr->id,
                'from_status'         => 'revision_required',
                'to_status'           => 'submitted',
                'user_id'             => $resubmitter->id,
                'notes'               => "Revisi Material: Anggaran mengalami perubahan dari Rp " . number_format($oldEstimatedTotal, 0, ',', '.') . " menjadi Rp " . number_format($newTotal, 0, ',', '.') . ". Seluruh persetujuan di-reset ulang ke Tahap 1 (Manager Divisi) demi kepatuhan governance anggaran.",
            ]);

            AuditTrailService::record(
                'pr_revision_resubmitted',
                $pr,
                $pr->pr_number,
                beforeState: ['total' => $oldEstimatedTotal, 'route' => 'full_reset'],
                afterState: ['total' => $newTotal, 'route' => 'tier_1'],
                description: "Revisi material diajukan ulang. Persetujuan di-reset ke Tingkat 1 (Manager Divisi)."
            );

            return [
                'type' => 'material_reset',
                'message' => "Pengajuan mengalami perubahan anggaran dan diserahkan kembali mulai dari Tahap 1 (Manager Divisi).",
            ];
        } else {
            // NON-MATERIAL / CLARIFICATION -> DIRECT RETURN TO REQUESTING APPROVER
            // Keep lower approved tiers intact!
            // Reset the revision_required tier back to 'pending'
            $revisionTier->update([
                'status' => 'pending',
                'acted_at' => null,
                'notes' => null,
            ]);

            StatusHistory::create([
                'purchase_request_id' => $pr->id,
                'from_status'         => 'revision_required',
                'to_status'           => 'submitted',
                'user_id'             => $resubmitter->id,
                'notes'               => "Pengajuan diperbaiki sesuai catatan [{$revisionTier->tier_label}]. Persetujuan tingkat sebelumnya tetap sah (valid). Dokumen langsung diteruskan kembali ke {$revisionTier->role_label}.",
            ]);

            AuditTrailService::record(
                'pr_revision_resubmitted',
                $pr,
                $pr->pr_number,
                beforeState: ['tier' => $revisionTier->tier_level, 'status' => 'revision_required'],
                afterState: ['tier' => $revisionTier->tier_level, 'status' => 'pending'],
                description: "Perbaikan non-anggaran diserahkan. Persetujuan sebelumnya tetap sah, pengajuan langsung kembali ke [{$revisionTier->tier_label}]."
            );

            // Notify the specific approver tier directly
            $this->notifyApproversForTier($pr, $revisionTier->tier_level);

            return [
                'type' => 'direct_return',
                'message' => "Pengajuan diperbaiki dan langsung diteruskan kembali ke {$revisionTier->role_label} tanpa mengulang persetujuan atasan sebelumnya.",
            ];
        }
    }

    /**
     * Reject PR on the active tier
     */
    public function reject(User $user, PurchaseRequest $pr, string $notes): void
    {
        if ($pr->status !== 'submitted') {
            abort(409, "Pengajuan #{$pr->pr_number} telah diproses atau statusnya telah berubah beberapa saat yang lalu.");
        }

        if (!$pr->currentPendingApproval()) {
            abort(409, "Tahap persetujuan untuk PR #{$pr->pr_number} telah selesai diproses oleh pejabat lain.");
        }

        if (!$this->canUserApprove($user, $pr)) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk menolak pengajuan ini.');
        }

        DB::transaction(function () use ($user, $pr, $notes) {
            // Pessimistic Locking: Lock PR row to serialize concurrent actions
            $lockedPr = PurchaseRequest::where('id', $pr->id)->lockForUpdate()->first();
            if (!$lockedPr || $lockedPr->status !== 'submitted') {
                abort(409, "Pengajuan #{$pr->pr_number} telah diproses atau statusnya telah berubah beberapa saat yang lalu.");
            }

            // Lock the active pending tier row
            $activeTier = $lockedPr->approvals()
                ->where('status', 'pending')
                ->orderBy('tier_level', 'asc')
                ->lockForUpdate()
                ->first();

            if (!$activeTier) {
                abort(409, "Tahap persetujuan untuk PR #{$pr->pr_number} telah selesai diproses oleh pejabat lain.");
            }

            if (!$this->canUserApproveTier($user, $lockedPr, $activeTier)) {
                abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang pada tahap persetujuan aktif saat ini.');
            }

            $delegator = $this->resolveActingDelegator($user, $activeTier, $lockedPr);
            $isActing = ($delegator !== null);
            $actingSuffix = $isActing ? " atas nama Plt {$delegator->name}" : "";

            $activeTier->update([
                'status'             => 'rejected',
                'approver_id'        => $user->id,
                'is_acting'          => $isActing,
                'acting_for_user_id' => $delegator?->id,
                'acted_at'           => now(),
                'notes'              => $notes,
            ]);

            $lockedPr->update(['status' => 'rejected']);

            StatusHistory::create([
                'purchase_request_id' => $lockedPr->id,
                'from_status'         => 'submitted',
                'to_status'           => 'rejected',
                'user_id'             => $user->id,
                'notes'               => "[{$activeTier->tier_label}] Ditolak oleh {$user->name}{$actingSuffix}: {$notes}",
            ]);

            // SHA-256 Audit Trail
            AuditTrailService::record(
                'pr_rejected',
                $lockedPr,
                $lockedPr->pr_number,
                beforeState: ['status' => 'submitted'],
                afterState: ['status' => 'rejected'],
                description: "Ditolak oleh {$user->name}{$actingSuffix}. Alasan: {$notes}"
            );

            // Multi-Channel Notification: Notify Requester
            NotificationService::send(
                $lockedPr->user,
                "Pengajuan PR #{$lockedPr->pr_number} Ditolak",
                "Pengajuan '{$lockedPr->title}' ditolak oleh atasan ({$user->name}{$actingSuffix}): {$notes}",
                route('purchase-requests.show', $lockedPr),
                'pr_rejected'
            );
        });
    }

    /**
     * Get PRs pending user's approval action (Approval Queue)
     */
    public function getPendingQueueForUser(User $user)
    {
        $submittedPrs = PurchaseRequest::with(['user', 'department', 'items', 'approvals.department'])
            ->where('status', 'submitted')
            ->where('user_id', '!=', $user->id) // Anti self-approval
            ->latest()
            ->get();

        return $submittedPrs->filter(function ($pr) use ($user) {
            return $this->canUserApprove($user, $pr);
        });
    }

    /**
     * Notify potential approvers and active Plt delegates for a given tier
     */
    private function notifyApproversForTier(PurchaseRequest $pr, int $tierLevel): void
    {
        $tier = $pr->approvals()->where('tier_level', $tierLevel)->first();
        if (!$tier) {
            return;
        }

        $query = User::query();

        if ($tier->role_required === 'manager') {
            $query->where('role', 'manager')->where('department_id', $pr->department_id);
            $tierTitle = "[Otorisasi Manager] Permintaan Persetujuan PR #{$pr->pr_number}";
        } elseif ($tier->role_required === 'finance') {
            $query->where('role', 'finance');
            $tierTitle = "[Otorisasi Keuangan] Verifikasi Anggaran PR #{$pr->pr_number}";
        } elseif ($tier->role_required === 'hod') {
            $query->where('role', 'hod');
            $tierTitle = "[Otorisasi Direksi/HoD] Persetujuan Pengadaan PR #{$pr->pr_number}";
        } else {
            return;
        }

        $approvers = $query->get();

        // Also check if any of these approvers have active Plt delegates!
        $approverIds = $approvers->pluck('id')->toArray();
        $pltDelegates = ActingDelegation::activeNow()
            ->whereIn('delegator_user_id', $approverIds)
            ->where('role_delegated', $tier->role_required)
            ->with('delegatee')
            ->get()
            ->map->delegatee
            ->filter();

        $allRecipients = $approvers->concat($pltDelegates)->unique('id');
        $approvalActionLink = route('purchase-requests.show', $pr) . '#approval-action';

        foreach ($allRecipients as $recipient) {
            if ($recipient->id !== $pr->user_id) {
                NotificationService::send(
                    $recipient,
                    $tierTitle,
                    "Pengajuan baru '{$pr->title}' dari {$pr->user?->name} ({$pr->department?->code}) senilai Rp " . number_format($pr->estimated_total, 0, ',', '.') . " menunggu persetujuan Anda pada tahap {$tier->tier_label}.",
                    $approvalActionLink,
                    'approval_needed'
                );
            }
        }
    }

    /**
     * Re-assign a pending approval tier to a new active approver (Handover / Resign / Mutasi)
     */
    public function reassignApproval(PrApproval $tier, User $newApprover, string $reason, User $performedBy): void
    {
        if ($tier->status !== 'pending') {
            throw new \InvalidArgumentException('Hanya tahapan persetujuan yang berstatus pending yang dapat dialihkan.');
        }

        if ($newApprover->is_active === false) {
            throw new \InvalidArgumentException('Akun pejabat baru berstatus nonaktif dan tidak dapat menerima pengalihan wewenang.');
        }

        $pr = $tier->purchaseRequest;

        // Anti self-approval check
        if ($newApprover->id === $pr->user_id) {
            throw new \InvalidArgumentException('Pejabat baru tidak boleh sama dengan pemohon pengajuan (Anti Self-Approval).');
        }

        $oldApproverName = $tier->assignedApprover?->name ?? ($tier->role_label . ' (' . ($tier->department?->code ?? 'Organisasi') . ')');

        DB::transaction(function () use ($tier, $newApprover, $reason, $performedBy, $pr, $oldApproverName) {
            $tier->update([
                'assigned_approver_id' => $newApprover->id,
                'reassigned_at'        => now(),
                'reassigned_by'        => $performedBy->id,
                'reassign_reason'      => $reason,
            ]);

            StatusHistory::create([
                'purchase_request_id' => $pr->id,
                'from_status'         => $pr->status,
                'to_status'           => $pr->status,
                'user_id'             => $performedBy->id,
                'notes'               => "Pengalihan Otorisasi [{$tier->tier_label}]: Administrator dialihkan ke {$newApprover->name} ({$newApprover->role_label}). Alasan: {$reason}",
            ]);

            AuditTrailService::record(
                'approval_reassigned',
                $tier,
                "PR #{$pr->pr_number} - Tier {$tier->tier_level}",
                beforeState: ['assigned_approver' => $oldApproverName],
                afterState: [
                    'assigned_approver' => $newApprover->name,
                    'assigned_approver_id' => $newApprover->id,
                    'reassigned_by' => $performedBy->name,
                    'reason' => $reason,
                ],
                description: "{$performedBy->name} mengalihkan wewenang otorisasi [{$tier->tier_label}] untuk PR {$pr->pr_number} ke {$newApprover->name}. Alasan: {$reason}"
            );

            // Notify newly assigned approver
            NotificationService::send(
                $newApprover,
                "[Pengalihan Wewenang] Mandat Otorisasi PR #{$pr->pr_number}",
                "Wewenang persetujuan PR #{$pr->pr_number} ('{$pr->title}') dialihkan kepada Anda oleh {$performedBy->name}. Alasan: {$reason}",
                route('purchase-requests.show', $pr) . '#approval-action',
                'approval_reassigned'
            );
        });
    }

    /**
     * Transfer all active pending approvals from a resigning/mutating user to a successor user
     */
    public function transferPendingApprovals(User $formerUser, User $successorUser, string $reason, User $performedBy): int
    {
        $pendingTiers = PrApproval::where('status', 'pending')
            ->whereHas('purchaseRequest', fn($q) => $q->where('status', 'submitted'))
            ->where(function ($q) use ($formerUser) {
                $q->where('assigned_approver_id', $formerUser->id)
                  ->orWhere(function ($sq) use ($formerUser) {
                      $sq->whereNull('assigned_approver_id')
                         ->where('role_required', $formerUser->role)
                         ->when($formerUser->department_id, fn($dq) => $dq->where('department_id', $formerUser->department_id));
                  });
            })
            ->get();

        $count = 0;
        foreach ($pendingTiers as $tier) {
            // Skip if successor is the requester of this PR
            if ($tier->purchaseRequest && $tier->purchaseRequest->user_id === $successorUser->id) {
                continue;
            }
            $this->reassignApproval($tier, $successorUser, $reason, $performedBy);
            $count++;
        }

        return $count;
    }
}
