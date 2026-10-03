<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use App\Services\ApprovalService;
use App\Services\AuditTrailService;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    protected ApprovalService $approvalService;

    public function __construct(ApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    /**
     * Approval Queue: List of PRs waiting for the logged-in user's approval
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Only Manager, Finance, HoD, Admin, or Auditor have approval queue access
        if (! $user->hasRole(['manager', 'finance', 'hod', 'admin', 'auditor'])) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki akses ke Antrean Persetujuan.');
        }

        $pendingPrs = $this->approvalService->getPendingQueueForUser($user);

        // Riwayat Persetujuan: Record of PRs previously approved, rejected, or revised by this user
        $historyApprovals = \App\Models\PrApproval::with(['purchaseRequest.user', 'purchaseRequest.department'])
            ->where('approver_id', $user->id)
            ->whereIn('status', ['approved', 'rejected', 'revision_required'])
            ->latest('acted_at')
            ->take(20)
            ->get();

        return view('approvals.index', compact('pendingPrs', 'historyApprovals'));
    }

    /**
     * Approve the current tier of the PR
     */
    public function approve(Request $request, PurchaseRequest $purchaseRequest)
    {
        $user = auth()->user();
        $oldStatus = $purchaseRequest->status;

        try {
            $this->approvalService->approve(
                $user,
                $purchaseRequest,
                $request->input('notes')
            );
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            if ($e->getStatusCode() === 409) {
                return redirect()->route('purchase-requests.show', $purchaseRequest)
                    ->with('error', $e->getMessage());
            }
            throw $e;
        }

        $purchaseRequest->refresh();
        AuditTrailService::record(
            action: 'pr_approved',
            entity: $purchaseRequest,
            entityLabel: $purchaseRequest->pr_number,
            beforeState: ['status' => $oldStatus],
            afterState: ['status' => $purchaseRequest->status],
            description: "{$user->name} menyetujui PR {$purchaseRequest->pr_number} sebagai {$user->role_label}.",
            comment: $request->input('notes'),
            request: $request,
        );

        return redirect()->route('purchase-requests.show', $purchaseRequest)
            ->with('success', "Persetujuan untuk PR #{$purchaseRequest->pr_number} berhasil dicatat.");
    }

    /**
     * Request revision with mandatory notes
     */
    public function requestRevision(Request $request, PurchaseRequest $purchaseRequest)
    {
        $request->validate([
            'notes' => ['required', 'string', 'min:5'],
        ], [
            'notes.required' => 'Catatan revisi wajib diisi agar pemohon memahami perbaikan yang dibutuhkan.',
            'notes.min' => 'Catatan revisi minimal 5 karakter.',
        ]);

        $user = auth()->user();
        $oldStatus = $purchaseRequest->status;

        try {
            $this->approvalService->requestRevision(
                $user,
                $purchaseRequest,
                $request->notes
            );
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            if ($e->getStatusCode() === 409) {
                return redirect()->route('purchase-requests.show', $purchaseRequest)
                    ->with('error', $e->getMessage());
            }
            throw $e;
        }

        AuditTrailService::record(
            action: 'pr_revision',
            entity: $purchaseRequest,
            entityLabel: $purchaseRequest->pr_number,
            beforeState: ['status' => $oldStatus],
            afterState: ['status' => 'revision_required'],
            description: "{$user->name} meminta revisi PR {$purchaseRequest->pr_number}.",
            comment: $request->notes,
            request: $request,
        );

        return redirect()->route('purchase-requests.show', $purchaseRequest)
            ->with('success', "Permintaan revisi PR #{$purchaseRequest->pr_number} telah dikirim ke pemohon.");
    }

    /**
     * Reject PR with mandatory reason
     */
    public function reject(Request $request, PurchaseRequest $purchaseRequest)
    {
        $request->validate([
            'notes' => ['required', 'string', 'min:5'],
        ], [
            'notes.required' => 'Alasan penolakan pengadaan wajib diisi sebagai bukti audit bisnis.',
            'notes.min' => 'Alasan penolakan minimal 5 karakter.',
        ]);

        $user = auth()->user();
        $oldStatus = $purchaseRequest->status;

        try {
            $this->approvalService->reject(
                $user,
                $purchaseRequest,
                $request->notes
            );
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            if ($e->getStatusCode() === 409) {
                return redirect()->route('purchase-requests.show', $purchaseRequest)
                    ->with('error', $e->getMessage());
            }
            throw $e;
        }

        AuditTrailService::record(
            action: 'pr_rejected',
            entity: $purchaseRequest,
            entityLabel: $purchaseRequest->pr_number,
            beforeState: ['status' => $oldStatus],
            afterState: ['status' => 'rejected'],
            description: "{$user->name} menolak PR {$purchaseRequest->pr_number}.",
            comment: $request->notes,
            request: $request,
        );

        return redirect()->route('purchase-requests.show', $purchaseRequest)
            ->with('success', "Pengajuan PR #{$purchaseRequest->pr_number} telah ditolak.");
    }

    /**
     * Re-assign pending approval tier to a new approver (Admin only)
     */
    public function reassign(Request $request, \App\Models\PrApproval $approval)
    {
        $user = auth()->user();
        if (! $user->hasRole('admin')) {
            abort(403, 'Hanya Administrator yang berwenang mengalihkan mandat persetujuan.');
        }

        $validated = $request->validate([
            'new_approver_id' => 'required|exists:users,id',
            'reassign_reason' => 'required|string|min:5|max:1000',
        ], [
            'new_approver_id.required' => 'Pilih pejabat pengganti yang akan menerima wewenang.',
            'reassign_reason.required' => 'Alasan pengalihan wewenang (seperti resign atau mutasi) wajib diisi.',
            'reassign_reason.min' => 'Alasan pengalihan minimal 5 karakter.',
        ]);

        $newApprover = \App\Models\User::findOrFail($validated['new_approver_id']);

        try {
            $this->approvalService->reassignApproval(
                $approval,
                $newApprover,
                $validated['reassign_reason'],
                $user
            );
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Wewenang otorisasi [{$approval->tier_label}] berhasil dialihkan kepada {$newApprover->name}.");
    }
}
