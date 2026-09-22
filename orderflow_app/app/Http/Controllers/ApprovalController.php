<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use App\Services\ApprovalService;
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

        // Only Manager, Finance, Admin, or Auditor have approval queue access
        if (! $user->hasRole(['manager', 'finance', 'admin', 'auditor'])) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki akses ke Antrean Persetujuan.');
        }

        $pendingPrs = $this->approvalService->getPendingQueueForUser($user);

        return view('approvals.index', compact('pendingPrs'));
    }

    /**
     * Approve the current tier of the PR
     */
    public function approve(Request $request, PurchaseRequest $purchaseRequest)
    {
        $user = auth()->user();

        $this->approvalService->approve(
            $user,
            $purchaseRequest,
            $request->input('notes')
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

        $this->approvalService->requestRevision(
            $user,
            $purchaseRequest,
            $request->notes
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

        $this->approvalService->reject(
            $user,
            $purchaseRequest,
            $request->notes
        );

        return redirect()->route('purchase-requests.show', $purchaseRequest)
            ->with('success', "Pengajuan PR #{$purchaseRequest->pr_number} telah ditolak.");
    }
}
