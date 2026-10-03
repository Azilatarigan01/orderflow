<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseRequestRequest;
use App\Models\Attachment;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\StatusHistory;
use App\Services\ApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PurchaseRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = PurchaseRequest::with(['user', 'department', 'items'])
            ->accessibleBy($user)
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('pr_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        $purchaseRequests = $query->paginate(10)->withQueryString();

        // Optimized single-pass aggregation for metrics widgets (eliminates 4 redundant queries)
        $metricsRow = PurchaseRequest::accessibleBy($user)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft,
                SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) as submitted,
                SUM(CASE WHEN status = 'revision_required' THEN 1 ELSE 0 END) as revision_required,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved
            ")
            ->first();

        $metrics = [
            'total' => (int) ($metricsRow->total ?? 0),
            'draft' => (int) ($metricsRow->draft ?? 0),
            'submitted' => (int) ($metricsRow->submitted ?? 0),
            'revision_required' => (int) ($metricsRow->revision_required ?? 0),
            'approved' => (int) ($metricsRow->approved ?? 0),
        ];

        return view('purchase_requests.index', compact('purchaseRequests', 'metrics'));
    }

    public function create()
    {
        return view('purchase_requests.create');
    }

    public function store(PurchaseRequestRequest $request)
    {
        $user = auth()->user();
        $isSubmitting = $request->input('action') === 'submit';
        $initialStatus = $isSubmitting ? 'submitted' : 'draft';

        $pr = DB::transaction(function () use ($request, $user, $initialStatus, $isSubmitting) {
            $prNumber = PurchaseRequest::generatePrNumber($user->department_id);

            $purchaseRequest = PurchaseRequest::create([
                'pr_number' => $prNumber,
                'user_id' => $user->id,
                'department_id' => $user->department_id,
                'title' => $request->title,
                'description' => $request->description,
                'required_date' => $request->required_date,
                'estimated_total' => 0,
                'status' => $initialStatus,
            ]);

            // Save line items and compute subtotal
            $total = 0;
            foreach ($request->items as $itemData) {
                $subtotal = $itemData['quantity'] * $itemData['estimated_unit_price'];
                $total += $subtotal;

                $purchaseRequest->items()->create([
                    'item_name' => $itemData['item_name'],
                    'specification' => $itemData['specification'] ?? null,
                    'quantity' => $itemData['quantity'],
                    'unit' => $itemData['unit'],
                    'estimated_unit_price' => $itemData['estimated_unit_price'],
                    'subtotal' => $subtotal,
                ]);
            }

            $purchaseRequest->update(['estimated_total' => $total]);

            // Handle file upload
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                // Store securely in private local disk for confidentiality
                $filePath = $file->store('private/pr_attachments', 'local');

                $attachment = Attachment::create([
                    'purchase_request_id' => $purchaseRequest->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $filePath,
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'uploaded_by' => $user->id,
                ]);

                $purchaseRequest->update(['attachment_path' => $filePath]);
            }

            // Record status history
            StatusHistory::create([
                'purchase_request_id' => $purchaseRequest->id,
                'from_status' => null,
                'to_status' => $initialStatus,
                'user_id' => $user->id,
                'notes' => $isSubmitting
                    ? 'Pengajuan baru langsung diserahkan untuk persetujuan bertingkat.'
                    : 'Draf pengajuan pembelian berhasil dibuat.',
            ]);

            // Generate approval tiers if directly submitted
            if ($isSubmitting) {
                app(ApprovalService::class)->generateApprovalTiers($purchaseRequest);
            }

            return $purchaseRequest;
        });

        $message = $isSubmitting
            ? "Purchase Request #{$pr->pr_number} berhasil dibuat dan diajukan ke antrean persetujuan."
            : "Draf Purchase Request #{$pr->pr_number} berhasil disimpan.";

        return redirect()->route('purchase-requests.show', $pr)->with('success', $message);
    }

    public function show(PurchaseRequest $purchaseRequest)
    {
        $user = auth()->user();

        // Department isolation authorization check
        if (!$this->isUserAuthorizedToView($user, $purchaseRequest)) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk melihat pengajuan dari divisi lain.');
        }

        $purchaseRequest->load([
            'user',
            'department',
            'items',
            'attachments.uploader',
            'histories.user',
            'approvals.department',
            'approvals.approver',
        ]);

        $canApprove = app(ApprovalService::class)->canUserApprove($user, $purchaseRequest);
        $activeTier = $purchaseRequest->currentPendingApproval();

        return view('purchase_requests.show', compact('purchaseRequest', 'canApprove', 'activeTier'));
    }

    public function edit(PurchaseRequest $purchaseRequest)
    {
        $user = auth()->user();

        if (!$purchaseRequest->canBeEditedBy($user)) {
            abort(403, 'Akses Ditolak: Pengajuan ini sudah tidak dapat diubah karena status bukan draf atau permintaan revisi.');
        }

        $purchaseRequest->load(['items', 'attachments']);

        return view('purchase_requests.edit', compact('purchaseRequest'));
    }

    public function update(PurchaseRequestRequest $request, PurchaseRequest $purchaseRequest)
    {
        $user = auth()->user();

        if (!$purchaseRequest->canBeEditedBy($user)) {
            abort(403, 'Akses Ditolak: Pengajuan ini tidak dapat diedit.');
        }

        $isSubmitting = $request->input('action') === 'submit';
        $oldStatus = $purchaseRequest->status;
        $oldEstimatedTotal = (float) $purchaseRequest->estimated_total;
        $newStatus = $isSubmitting ? 'submitted' : $oldStatus;

        DB::transaction(function () use ($request, $purchaseRequest, $user, $oldStatus, $newStatus, $isSubmitting, $oldEstimatedTotal) {
            $purchaseRequest->update([
                'title' => $request->title,
                'description' => $request->description,
                'required_date' => $request->required_date,
                'status' => $newStatus,
            ]);

            // Replace items
            $purchaseRequest->items()->delete();
            $total = 0;
            foreach ($request->items as $itemData) {
                $subtotal = $itemData['quantity'] * $itemData['estimated_unit_price'];
                $total += $subtotal;

                $purchaseRequest->items()->create([
                    'item_name' => $itemData['item_name'],
                    'specification' => $itemData['specification'] ?? null,
                    'quantity' => $itemData['quantity'],
                    'unit' => $itemData['unit'],
                    'estimated_unit_price' => $itemData['estimated_unit_price'],
                    'subtotal' => $subtotal,
                ]);
            }

            $purchaseRequest->update(['estimated_total' => $total]);

            // Handle new attachment upload if provided
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                // Store securely in private local disk for confidentiality
                $filePath = $file->store('private/pr_attachments', 'local');

                Attachment::create([
                    'purchase_request_id' => $purchaseRequest->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $filePath,
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'uploaded_by' => $user->id,
                ]);

                $purchaseRequest->update(['attachment_path' => $filePath]);
            }

            // Record status history and route approvals
            if ($isSubmitting && $oldStatus !== 'submitted') {
                if ($oldStatus === 'revision_required') {
                    app(ApprovalService::class)->handleResubmission($purchaseRequest, $oldEstimatedTotal, $user);
                } else {
                    StatusHistory::create([
                        'purchase_request_id' => $purchaseRequest->id,
                        'from_status' => $oldStatus,
                        'to_status' => 'submitted',
                        'user_id' => $user->id,
                        'notes' => 'Draf pengajuan diserahkan untuk persetujuan atasan.',
                    ]);

                    app(ApprovalService::class)->generateApprovalTiers($purchaseRequest);
                }
            }
        });

        $message = $isSubmitting
            ? "Purchase Request #{$purchaseRequest->pr_number} berhasil diperbarui dan diajukan untuk persetujuan."
            : "Perubahan Purchase Request #{$purchaseRequest->pr_number} berhasil disimpan.";

        return redirect()->route('purchase-requests.show', $purchaseRequest)->with('success', $message);
    }

    public function submit(Request $request, PurchaseRequest $purchaseRequest)
    {
        $user = auth()->user();

        if (!$purchaseRequest->canBeSubmittedBy($user)) {
            return back()->with('error', 'Pengajuan tidak dapat dikirim tanpa item atau status tidak valid.');
        }

        $oldStatus = $purchaseRequest->status;
        $oldEstimatedTotal = (float) $purchaseRequest->estimated_total;

        DB::transaction(function () use ($purchaseRequest, $user, $oldStatus, $request, $oldEstimatedTotal) {
            $purchaseRequest->update(['status' => 'submitted']);

            if ($oldStatus === 'revision_required') {
                app(ApprovalService::class)->handleResubmission($purchaseRequest, $oldEstimatedTotal, $user);
            } else {
                StatusHistory::create([
                    'purchase_request_id' => $purchaseRequest->id,
                    'from_status' => $oldStatus,
                    'to_status' => 'submitted',
                    'user_id' => $user->id,
                    'notes' => $request->input('notes', 'Pengajuan diserahkan oleh pemohon untuk peninjauan atasan.'),
                ]);

                app(ApprovalService::class)->generateApprovalTiers($purchaseRequest);
            }
        });

        return redirect()->route('purchase-requests.show', $purchaseRequest)->with('success', "Purchase Request #{$purchaseRequest->pr_number} berhasil diajukan untuk persetujuan.");
    }

    /**
     * Poin 4: Tarik Pengajuan ke Draf (Recall / Withdraw)
     * Pemohon dapat menarik PR yang sedang menunggu approval untuk mengeditnya kembali tanpa harus ditolak manual.
     */
    public function withdraw(Request $request, PurchaseRequest $purchaseRequest)
    {
        $user = auth()->user();

        if (!$purchaseRequest->canBeWithdrawnBy($user)) {
            return back()->with('error', 'Pengajuan ini tidak dapat ditarik kembali.');
        }

        // Check if any approval tier has already been approved
        $hasApprovedTiers = $purchaseRequest->approvals()->where('status', 'approved')->exists();
        if ($hasApprovedTiers) {
            return back()->with('error', 'Pengajuan tidak dapat ditarik karena sudah ada tingkat persetujuan yang menyetujui.');
        }

        DB::transaction(function () use ($purchaseRequest, $user, $request) {
            // Dismiss pending approvals
            $purchaseRequest->approvals()->delete();

            $purchaseRequest->update(['status' => 'draft']);

            StatusHistory::create([
                'purchase_request_id' => $purchaseRequest->id,
                'from_status' => 'submitted',
                'to_status' => 'draft',
                'user_id' => $user->id,
                'notes' => $request->input('notes', 'Pengajuan ditarik kembali (recall) ke Draf oleh pemohon untuk perbaikan/revisi mandiri.'),
            ]);
        });

        return redirect()->route('purchase-requests.show', $purchaseRequest)
            ->with('success', "Purchase Request #{$purchaseRequest->pr_number} berhasil ditarik kembali ke status Draf. Anda dapat mengubah data dan mengajukannya ulang.");
    }

    /**
     * Poin 4: Batalkan Pengajuan (Cancel PR)
     */
    public function cancel(Request $request, PurchaseRequest $purchaseRequest)
    {
        $user = auth()->user();

        if (!$purchaseRequest->canBeCancelledBy($user)) {
            return back()->with('error', 'Pengajuan dengan status saat ini tidak dapat dibatalkan.');
        }

        $oldStatus = $purchaseRequest->status;

        DB::transaction(function () use ($purchaseRequest, $user, $oldStatus, $request) {
            // Delete pending approvals
            $purchaseRequest->approvals()->where('status', 'pending')->delete();

            $purchaseRequest->update(['status' => 'cancelled']);

            StatusHistory::create([
                'purchase_request_id' => $purchaseRequest->id,
                'from_status' => $oldStatus,
                'to_status' => 'cancelled',
                'user_id' => $user->id,
                'notes' => $request->input('cancellation_reason', 'Pengajuan dibatalkan secara mandiri oleh pemohon.'),
            ]);
        });

        return redirect()->route('purchase-requests.show', $purchaseRequest)
            ->with('success', "Purchase Request #{$purchaseRequest->pr_number} berhasil dibatalkan.");
    }

    public function destroy(PurchaseRequest $purchaseRequest)
    {
        $user = auth()->user();

        if ($purchaseRequest->user_id !== $user->id && !$user->hasRole('admin')) {
            abort(403, 'Akses Ditolak.');
        }

        if ($purchaseRequest->status !== 'draft') {
            return back()->with('error', 'Hanya pengajuan berstatus draf yang dapat dihapus.');
        }

        $prNumber = $purchaseRequest->pr_number;
        $purchaseRequest->delete();

        return redirect()->route('purchase-requests.index')->with('success', "Draf Purchase Request #{$prNumber} telah dihapus.");
    }

    private function isUserAuthorizedToView($user, PurchaseRequest $purchaseRequest): bool
    {
        // Sovereign & oversight roles
        if ($user->hasRole(['admin', 'procurement', 'auditor'])) {
            return true;
        }

        // Creator of the PR can always view it
        if ($purchaseRequest->user_id === $user->id) {
            return true;
        }

        // Finance & Accounting verifies company-wide non-draft PRs (including rejected and revision_required)
        if ($user->hasRole('finance')) {
            return $purchaseRequest->status !== 'draft';
        }

        // HoD (Head of Department / Direksi) can view strategic PRs > 25M or PRs from their own department
        if ($user->hasRole('hod')) {
            if ($purchaseRequest->estimated_total > 25000000 && $purchaseRequest->status !== 'draft') {
                return true;
            }
            return $purchaseRequest->department_id === $user->department_id && $purchaseRequest->status !== 'draft';
        }

        // Department Manager can view non-draft PRs from their department or via active Plt
        if ($user->hasRole('manager')) {
            $isDeptManager = ($user->department_id === $purchaseRequest->department_id && $purchaseRequest->status !== 'draft');
            $hasPlt = $user->getActiveActingDelegationFor('manager', $purchaseRequest->department_id) !== null;
            return $isDeptManager || $hasPlt;
        }

        // Any user who has an active/past approval tier on this PR
        if ($purchaseRequest->approvals()->where('approver_id', $user->id)->exists()) {
            return true;
        }

        // Regular Requester / Warehouse: strictly ONLY their own PR
        return false;
    }

    /**
     * Poin 5: Secure Stream Document for PR Attachments
     */
    public function downloadAttachment(PurchaseRequest $purchaseRequest)
    {
        $user = auth()->user();

        if (!$this->isUserAuthorizedToView($user, $purchaseRequest)) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk mengunduh lampiran pengajuan ini.');
        }

        if (!$purchaseRequest->attachment_path) {
            abort(404, 'Lampiran pengajuan tidak ditemukan.');
        }

        $disk = Storage::disk('local')->exists($purchaseRequest->attachment_path) ? 'local' : 'public';
        if (!Storage::disk($disk)->exists($purchaseRequest->attachment_path)) {
            abort(404, 'Berkas fisik lampiran tidak ditemukan di storage server.');
        }

        return Storage::disk($disk)->response($purchaseRequest->attachment_path);
    }
}
