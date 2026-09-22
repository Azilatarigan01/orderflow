<?php

namespace App\Services;

use App\Models\InAppNotification;
use App\Models\PrApproval;
use App\Models\PurchaseRequest;
use App\Models\StatusHistory;
use App\Models\User;
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

        // Notify Tier 1 approvers
        $this->notifyApproversForTier($pr, 1);
    }

    /**
     * Check if a user is allowed to act on the current pending tier
     */
    public function canUserApprove(User $user, PurchaseRequest $pr): bool
    {
        if ($pr->status !== 'submitted') {
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

        // Admin can approve any tier for emergency governance
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($activeTier->role_required === 'manager') {
            return $user->hasRole('manager') && $user->department_id === $pr->department_id;
        }

        if ($activeTier->role_required === 'finance') {
            return $user->hasRole('finance');
        }

        if ($activeTier->role_required === 'hod') {
            return $user->hasRole('admin') || $user->hasRole('auditor');
        }

        return false;
    }

    /**
     * Approve the current active tier
     */
    public function approve(User $user, PurchaseRequest $pr, ?string $notes = null): void
    {
        if (!$this->canUserApprove($user, $pr)) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk menyetujui tahap pengajuan ini atau tidak dapat menyetujui pengajuan milik sendiri.');
        }

        DB::transaction(function () use ($user, $pr, $notes) {
            $activeTier = $pr->currentPendingApproval();

            $activeTier->update([
                'status' => 'approved',
                'approver_id' => $user->id,
                'acted_at' => now(),
                'notes' => $notes ?: 'Persetujuan disetujui sesuai otorisasi.',
            ]);

            // Check if there is a next pending tier
            $nextTier = $pr->currentPendingApproval();

            if ($nextTier) {
                // PR remains submitted, waiting for next tier
                StatusHistory::create([
                    'purchase_request_id' => $pr->id,
                    'from_status' => 'submitted',
                    'to_status' => 'submitted',
                    'user_id' => $user->id,
                    'notes' => "[{$activeTier->tier_label}] Disetujui oleh {$user->name}. Menunggu {$nextTier->tier_label}.",
                ]);

                // Notify next tier approvers
                $this->notifyApproversForTier($pr, $nextTier->tier_level);
            } else {
                // Final approval reached!
                $pr->update(['status' => 'approved']);

                StatusHistory::create([
                    'purchase_request_id' => $pr->id,
                    'from_status' => 'submitted',
                    'to_status' => 'approved',
                    'user_id' => $user->id,
                    'notes' => "[{$activeTier->tier_label}] Persetujuan akhir tuntas oleh {$user->name}. Pengadaan siap diproses tim Procurement.",
                ]);

                // Notify Requester
                InAppNotification::create([
                    'user_id' => $pr->user_id,
                    'title' => "Pengajuan PR #{$pr->pr_number} Telah Disetujui Penuh!",
                    'message' => "Pengajuan pembelian '{$pr->title}' telah disetujui oleh seluruh pihak yang berwenang dan diteruskan ke Pengadaan.",
                    'link' => route('purchase-requests.show', $pr),
                    'type' => 'pr_approved',
                ]);

                // Notify Procurement officers
                $procurementUsers = User::where('role', 'procurement')->get();
                foreach ($procurementUsers as $procUser) {
                    InAppNotification::create([
                        'user_id' => $procUser->id,
                        'title' => "PR Baru Siap Diproses: #{$pr->pr_number}",
                        'message' => "Pengajuan '{$pr->title}' senilai Rp " . number_format($pr->estimated_total, 0, ',', '.') . " telah disetujui penuh.",
                        'link' => route('purchase-requests.show', $pr),
                        'type' => 'pr_approved',
                    ]);
                }
            }
        });
    }

    /**
     * Request revision on the active tier
     */
    public function requestRevision(User $user, PurchaseRequest $pr, string $notes): void
    {
        if (!$this->canUserApprove($user, $pr)) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk meminta revisi pada pengajuan ini.');
        }

        DB::transaction(function () use ($user, $pr, $notes) {
            $activeTier = $pr->currentPendingApproval();

            $activeTier->update([
                'status' => 'revision_required',
                'approver_id' => $user->id,
                'acted_at' => now(),
                'notes' => $notes,
            ]);

            $pr->update(['status' => 'revision_required']);

            StatusHistory::create([
                'purchase_request_id' => $pr->id,
                'from_status' => 'submitted',
                'to_status' => 'revision_required',
                'user_id' => $user->id,
                'notes' => "[{$activeTier->tier_label}] Permintaan revisi oleh {$user->name}: {$notes}",
            ]);

            // Notify Requester
            InAppNotification::create([
                'user_id' => $pr->user_id,
                'title' => "Permintaan Revisi PR #{$pr->pr_number}",
                'message' => "Atasan meminta perbaikan pada '{$pr->title}': {$notes}",
                'link' => route('purchase-requests.edit', $pr),
                'type' => 'pr_revision',
            ]);
        });
    }

    /**
     * Reject PR on the active tier
     */
    public function reject(User $user, PurchaseRequest $pr, string $notes): void
    {
        if (!$this->canUserApprove($user, $pr)) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk menolak pengajuan ini.');
        }

        DB::transaction(function () use ($user, $pr, $notes) {
            $activeTier = $pr->currentPendingApproval();

            $activeTier->update([
                'status' => 'rejected',
                'approver_id' => $user->id,
                'acted_at' => now(),
                'notes' => $notes,
            ]);

            $pr->update(['status' => 'rejected']);

            StatusHistory::create([
                'purchase_request_id' => $pr->id,
                'from_status' => 'submitted',
                'to_status' => 'rejected',
                'user_id' => $user->id,
                'notes' => "[{$activeTier->tier_label}] Ditolak oleh {$user->name}: {$notes}",
            ]);

            // Notify Requester
            InAppNotification::create([
                'user_id' => $pr->user_id,
                'title' => "Pengajuan PR #{$pr->pr_number} Ditolak",
                'message' => "Pengajuan '{$pr->title}' ditolak oleh atasan: {$notes}",
                'link' => route('purchase-requests.show', $pr),
                'type' => 'pr_rejected',
            ]);
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
     * Notify potential approvers for a given tier
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
        } elseif ($tier->role_required === 'finance') {
            $query->where('role', 'finance');
        } elseif ($tier->role_required === 'hod') {
            $query->where('role', 'admin');
        }

        $approvers = $query->get();

        foreach ($approvers as $approver) {
            if ($approver->id !== $pr->user_id) {
                InAppNotification::create([
                    'user_id' => $approver->id,
                    'title' => "Permintaan Persetujuan PR #{$pr->pr_number}",
                    'message' => "Pengajuan baru '{$pr->title}' dari {$pr->user?->name} ({$pr->department?->code}) senilai Rp " . number_format($pr->estimated_total, 0, ',', '.') . " menunggu persetujuan Anda.",
                    'link' => route('purchase-requests.show', $pr),
                    'type' => 'approval_needed',
                ]);
            }
        }
    }
}
