<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseRequestRequest;
use App\Models\Attachment;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\StatusHistory;
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

        // Metrics for summary widgets
        $metrics = [
            'total' => PurchaseRequest::accessibleBy($user)->count(),
            'draft' => PurchaseRequest::accessibleBy($user)->where('status', 'draft')->count(),
            'submitted' => PurchaseRequest::accessibleBy($user)->where('status', 'submitted')->count(),
            'revision_required' => PurchaseRequest::accessibleBy($user)->where('status', 'revision_required')->count(),
            'approved' => PurchaseRequest::accessibleBy($user)->where('status', 'approved')->count(),
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
            $prNumber = PurchaseRequest::generatePrNumber();

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
                $filePath = $file->store('pr_attachments', 'public');

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
                    ? 'Pengajuan baru langsung diserahkan untuk persetujuan atasan.'
                    : 'Draf pengajuan pembelian berhasil dibuat.',
            ]);

            return $purchaseRequest;
        });

        $message = $isSubmitting
            ? "Purchase Request #{$pr->pr_number} berhasil dibuat dan langsung diajukan untuk persetujuan atasan."
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
        ]);

        return view('purchase_requests.show', compact('purchaseRequest'));
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
        $newStatus = $isSubmitting ? 'submitted' : $oldStatus;

        DB::transaction(function () use ($request, $purchaseRequest, $user, $oldStatus, $newStatus, $isSubmitting) {
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
                $filePath = $file->store('pr_attachments', 'public');

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

            // Record status history if transitioned or revised
            if ($isSubmitting && $oldStatus !== 'submitted') {
                StatusHistory::create([
                    'purchase_request_id' => $purchaseRequest->id,
                    'from_status' => $oldStatus,
                    'to_status' => 'submitted',
                    'user_id' => $user->id,
                    'notes' => $oldStatus === 'revision_required'
                        ? 'Pengajuan telah diperbaiki dan diserahkan kembali untuk persetujuan.'
                        : 'Draf pengajuan diserahkan untuk persetujuan atasan.',
                ]);
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

        DB::transaction(function () use ($purchaseRequest, $user, $oldStatus, $request) {
            $purchaseRequest->update(['status' => 'submitted']);

            StatusHistory::create([
                'purchase_request_id' => $purchaseRequest->id,
                'from_status' => $oldStatus,
                'to_status' => 'submitted',
                'user_id' => $user->id,
                'notes' => $request->input('notes', 'Pengajuan diserahkan oleh pemohon untuk peninjauan atasan.'),
            ]);
        });

        return redirect()->route('purchase-requests.show', $purchaseRequest)->with('success', "Purchase Request #{$purchaseRequest->pr_number} berhasil diajukan untuk persetujuan.");
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
        if ($user->hasRole(['admin', 'procurement', 'finance', 'auditor'])) {
            return true;
        }

        if ($user->hasRole('manager')) {
            return $user->department_id === $purchaseRequest->department_id;
        }

        // Requester: only same department or self
        return $purchaseRequest->user_id === $user->id || $user->department_id === $purchaseRequest->department_id;
    }
}
