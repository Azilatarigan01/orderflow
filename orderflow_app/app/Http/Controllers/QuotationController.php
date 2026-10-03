<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuotationRequest;
use App\Models\InAppNotification;
use App\Models\PurchaseRequest;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\StatusHistory;
use App\Models\Vendor;
use App\Services\AuditTrailService;
use App\Services\QuotationScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class QuotationController extends Controller
{
    public function __construct(
        protected QuotationScoringService $scoringService
    ) {}

    /**
     * RFQ and Quotations Hub (Procurement Overview)
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = PurchaseRequest::with(['user', 'department', 'quotations.vendor'])
            ->whereIn('status', ['approved', 'processing', 'completed'])
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

        $metrics = [
            'total_approved' => PurchaseRequest::where('status', 'approved')->count(),
            'needs_rfq' => PurchaseRequest::where('status', 'approved')->whereDoesntHave('quotations')->count(),
            'has_quotations' => PurchaseRequest::where('status', 'approved')->has('quotations')->count(),
            'vendor_awarded' => PurchaseRequest::whereIn('status', ['processing', 'completed'])->count(),
        ];

        return view('quotations.index', compact('purchaseRequests', 'metrics'));
    }

    /**
     * Quotation Comparison Screen (Matriks Perbandingan Vendor)
     */
    public function compare(PurchaseRequest $purchaseRequest)
    {
        $purchaseRequest->load(['items', 'user', 'department', 'quotations.vendor', 'quotations.items']);

        // Evaluate scores and normalize criteria
        $evaluation = $this->scoringService->evaluateQuotations($purchaseRequest);

        $ruleRequiresTwoQuotations = ($purchaseRequest->estimated_total > 10000000);
        $quotationCount = $purchaseRequest->quotations->count();
        $hasEnoughQuotations = !$ruleRequiresTwoQuotations || ($quotationCount >= 2);

        $selectedQuotation = $purchaseRequest->selectedQuotation();

        return view('quotations.compare', [
            'purchaseRequest' => $purchaseRequest,
            'quotations' => $evaluation['scored_quotations'],
            'bestScoreId' => $evaluation['best_score_id'],
            'minPrice' => $evaluation['min_price'],
            'minDays' => $evaluation['min_days'],
            'maxWarranty' => $evaluation['max_warranty'],
            'ruleRequiresTwoQuotations' => $ruleRequiresTwoQuotations,
            'quotationCount' => $quotationCount,
            'hasEnoughQuotations' => $hasEnoughQuotations,
            'selectedQuotation' => $selectedQuotation,
            'methodology' => $evaluation['methodology'] ?? [],
        ]);
    }

    /**
     * Create quotation form for a PR
     */
    public function create(PurchaseRequest $purchaseRequest)
    {
        $purchaseRequest->load('items');

        // Only active vendors
        $existingVendorIds = $purchaseRequest->quotations()->pluck('vendor_id')->toArray();
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $isRfqExpired = $purchaseRequest->is_rfq_closed;

        return view('quotations.create', compact('purchaseRequest', 'vendors', 'existingVendorIds', 'isRfqExpired'));
    }

    /**
     * Update RFQ deadline for a PR
     */
    public function updateRfqDeadline(Request $request, PurchaseRequest $purchaseRequest)
    {
        $request->validate([
            'rfq_deadline' => ['nullable', 'date'],
        ]);

        $purchaseRequest->update([
            'rfq_deadline' => $request->rfq_deadline,
        ]);

        $msg = $request->rfq_deadline 
            ? "Batas waktu penawaran (RFQ Deadline) berhasil diperbarui menjadi {$purchaseRequest->fresh()->formatted_rfq_deadline}."
            : "Batas waktu penawaran (RFQ Deadline) dinonaktifkan (Open Tender).";

        return back()->with('success', $msg);
    }

    /**
     * Store quotation and line items
     */
    public function store(QuotationRequest $request, PurchaseRequest $purchaseRequest)
    {
        $user = auth()->user();

        // RFQ Deadline check & late dispensation validation
        if ($purchaseRequest->rfq_deadline && now()->isAfter($purchaseRequest->rfq_deadline)) {
            $allowLate = $request->boolean('allow_late_submission');
            $reason = trim((string) $request->input('late_dispensation_reason'));

            if (!$allowLate || strlen($reason) < 10) {
                return back()->withInput()->withErrors([
                    'rfq_deadline' => "Batas waktu RFQ untuk PR ini telah berakhir pada {$purchaseRequest->formatted_rfq_deadline}. Penerimaan penawaran yang masuk melewati batas waktu wajib mencentang 'Dispensasi Keterlambatan' dan menyertakan alasan justifikasi (minimal 10 karakter)."
                ]);
            }

            $submissionStatus = 'late_with_dispensation';
            $lateReason = $reason;
        } else {
            $submissionStatus = 'on_time';
            $lateReason = null;
        }

        $quotation = DB::transaction(function () use ($request, $purchaseRequest, $user, $submissionStatus, $lateReason) {
            $subtotal = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $itemSubtotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $itemSubtotal;

                $itemsData[] = [
                    'purchase_request_item_id' => $item['purchase_request_item_id'] ?? null,
                    'item_name' => $item['item_name'],
                    'specification' => $item['specification'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $itemSubtotal,
                ];
            }

            $shippingCost = (float) $request->shipping_cost;
            $taxAmount = (float) $request->tax_amount;
            $grandTotal = $subtotal + $shippingCost + $taxAmount;

            $filePath = null;
            if ($request->hasFile('attachment')) {
                // Store in private local disk for confidentiality & compliance
                $filePath = $request->file('attachment')->store('private/quotations', 'local');
            }

            $quotation = Quotation::create([
                'purchase_request_id' => $purchaseRequest->id,
                'vendor_id' => $request->vendor_id,
                'quotation_number' => $request->quotation_number,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'estimated_delivery_days' => $request->estimated_delivery_days,
                'warranty_months' => $request->warranty_months ?? 0,
                'warranty_info' => $request->warranty_info,
                'valid_until' => $request->valid_until,
                'submission_status' => $submissionStatus,
                'late_dispensation_reason' => $lateReason,
                'file_path' => $filePath,
                'notes' => $request->notes,
                'created_by' => $user->id,
            ]);

            foreach ($itemsData as $data) {
                $quotation->items()->create($data);
            }

            // Recalculate scores for this PR
            $this->scoringService->evaluateQuotations($purchaseRequest);

            return $quotation;
        });

        $lateNotice = $quotation->is_late_submission ? " (Tercatat sebagai Dispensasi Keterlambatan)" : "";

        return redirect()->route('quotations.compare', $purchaseRequest)
            ->with('success', "Penawaran dari {$quotation->vendor?->name} berhasil dicatat{$lateNotice}.");
    }

    /**
     * Award RFQ to selected vendor
     */
    public function selectVendor(Request $request, PurchaseRequest $purchaseRequest)
    {
        $requiresTwoQuotations = ($purchaseRequest->estimated_total > 10000000);
        $totalQuotations = $purchaseRequest->quotations()->count();
        $isSingleSource = $request->boolean('is_single_source');

        // Validation rules
        $rules = [
            'quotation_id' => ['required', 'exists:quotations,id'],
            'selection_reason' => ['required', 'string', 'min:10'],
        ];

        $messages = [
            'quotation_id.required' => 'Pilih salah satu penawaran vendor pemenang.',
            'selection_reason.required' => 'Alasan pertimbangan keputusan pemilihan vendor wajib diisi.',
            'selection_reason.min' => 'Alasan keputusan minimal 10 karakter.',
        ];

        if ($requiresTwoQuotations && $totalQuotations < 2 && !$isSingleSource) {
            return back()->withErrors([
                'selection' => 'Aturan Pengadaan: PR di atas Rp10.000.000 wajib memiliki minimal 2 quotation vendor pembanding, kecuali jika terdapat alasan pemilihan penyedia tunggal (Single Source).'
            ])->withInput();
        }

        if ($isSingleSource) {
            $rules['single_source_reason'] = ['required', 'string', 'min:10'];
            $rules['single_source_category'] = ['required', 'in:emergency,sole_distributor,standardization,urgent_operational'];
            $rules['single_source_memo_number'] = ['required', 'string', 'min:3', 'max:100'];
            $rules['single_source_approver_name'] = ['required', 'string', 'min:3', 'max:150'];

            $messages['single_source_reason.required'] = 'Alasan justifikasi penetapan Penyedia Tunggal (Single Source) wajib dijelaskan.';
            $messages['single_source_reason.min'] = 'Alasan Single Source minimal 10 karakter.';
            $messages['single_source_category.required'] = 'Kategori diskresi Single Source wajib dipilih.';
            $messages['single_source_memo_number.required'] = 'Nomor Nota Dinas / Surat Persetujuan Direksi wajib diisi untuk kepatuhan audit.';
            $messages['single_source_approver_name.required'] = 'Nama Pejabat / Direktur yang memberikan otorisasi Single Source wajib diisi.';
        }

        $request->validate($rules, $messages);

        $selectedQuotation = Quotation::where('purchase_request_id', $purchaseRequest->id)
            ->where('id', $request->quotation_id)
            ->firstOrFail();

        // Check if selected vendor is active
        if (!$selectedQuotation->vendor || !$selectedQuotation->vendor->is_active) {
            return back()->withErrors([
                'selection' => 'Vendor yang dipilih berstatus nonaktif dan tidak dapat ditetapkan sebagai pemenang.'
            ]);
        }

        $user = auth()->user();
        $isSplitAward = $request->boolean('is_split_award');

        DB::transaction(function () use ($purchaseRequest, $selectedQuotation, $request, $isSingleSource, $isSplitAward, $user) {
            // Only deselect other quotations if not in multi-vendor / split award mode
            if (!$isSplitAward) {
                $purchaseRequest->quotations()->where('id', '!=', $selectedQuotation->id)->update([
                    'is_selected' => false,
                    'selection_reason' => null,
                    'is_single_source' => false,
                    'single_source_reason' => null,
                    'single_source_category' => null,
                    'single_source_memo_number' => null,
                    'single_source_approver_name' => null,
                ]);
            }

            // Select chosen quotation
            $selectedQuotation->update([
                'is_selected' => true,
                'selection_reason' => $request->selection_reason,
                'is_single_source' => $isSingleSource,
                'single_source_reason' => $isSingleSource ? $request->single_source_reason : null,
                'single_source_category' => $isSingleSource ? $request->single_source_category : null,
                'single_source_memo_number' => $isSingleSource ? $request->single_source_memo_number : null,
                'single_source_approver_name' => $isSingleSource ? $request->single_source_approver_name : null,
            ]);

            // Update PR status to 'processing'
            $oldStatus = $purchaseRequest->status;
            $purchaseRequest->update(['status' => 'processing']);

            // Record status history audit trail
            $vendorName = $selectedQuotation->vendor->name;
            $awardType = $isSplitAward ? "Pemenang Multi-Vendor (Split PO)" : "Pemenang Pengadaan";
            $notes = "Procurement menetapkan vendor: {$vendorName} (#{$selectedQuotation->quotation_number}) sebagai {$awardType} senilai {$selectedQuotation->formatted_grand_total}. Alasan: {$request->selection_reason}";
            if ($isSingleSource) {
                $catLabel = $selectedQuotation->single_source_category_label;
                $notes .= " [Single Source Diskresi: {$catLabel} | Memo: {$request->single_source_memo_number} | Otorisasi: {$request->single_source_approver_name} | Justifikasi: {$request->single_source_reason}]";
            }

            StatusHistory::create([
                'purchase_request_id' => $purchaseRequest->id,
                'from_status' => $oldStatus,
                'to_status' => 'processing',
                'user_id' => $user->id,
                'notes' => $notes,
            ]);

            // Notify Requester
            InAppNotification::create([
                'user_id' => $purchaseRequest->user_id,
                'title' => "Vendor Ditetapkan untuk PR #{$purchaseRequest->pr_number}",
                'message' => "Tim Procurement telah memilih '{$vendorName}' untuk pengadaan '{$purchaseRequest->title}'.",
                'link' => route('purchase-requests.show', $purchaseRequest),
                'type' => 'vendor_awarded',
            ]);
        });

        return redirect()->route('quotations.compare', $purchaseRequest)
            ->with('success', "Vendor {$selectedQuotation->vendor->name} berhasil ditetapkan sebagai pemenang pengadaan.");
    }

    /**
     * Delete a quotation
     */
    public function destroy(Quotation $quotation)
    {
        $pr = $quotation->purchaseRequest;

        if ($quotation->is_selected) {
            return back()->with('error', 'Penawaran ini telah ditetapkan sebagai pemenang dan tidak dapat dihapus sebelum status pemilihan diubah.');
        }

        if ($quotation->file_path) {
            Storage::disk('public')->delete($quotation->file_path);
        }

        $vendorName = $quotation->vendor?->name ?? 'Vendor';
        $quotation->delete();

        // Recalculate remaining
        $this->scoringService->evaluateQuotations($pr);

        return back()->with('success', "Penawaran dari {$vendorName} telah dihapus.");
    }

    /**
     * Poin 5: Secure Stream Document for Quotations
     * Hanya boleh diunduh/dilihat oleh Procurement, Admin, Finance, Auditor, atau Requester pemilik PR terkait.
     */
    public function downloadAttachment(Quotation $quotation)
    {
        $user = auth()->user();

        // Authorization check
        $purchaseRequest = $quotation->purchaseRequest;
        $isAuthorized = $user->hasRole(['procurement', 'admin', 'auditor', 'finance'])
            || ($purchaseRequest && $purchaseRequest->user_id === $user->id);

        if (!$isAuthorized) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk melihat berkas rahasia penawaran vendor ini.');
        }

        if (!$quotation->file_path) {
            abort(404, 'Berkas penawaran tidak ditemukan.');
        }

        // Support both old public disk paths and new local private disk paths seamlessly
        $disk = Storage::disk('local')->exists($quotation->file_path) ? 'local' : 'public';
        if (!Storage::disk($disk)->exists($quotation->file_path)) {
            abort(404, 'File dokumen tidak ditemukan di server.');
        }

        return Storage::disk($disk)->response($quotation->file_path);
    }

    /**
     * Fail / cancel RFQ tender and return PR to requester for revision
     */
    public function failTender(Request $request, PurchaseRequest $purchaseRequest)
    {
        $user = auth()->user();
        if (! $user->hasRole(['procurement', 'admin'])) {
            abort(403, 'Hanya tim Procurement atau Administrator yang berwenang membatalkan tender pengadaan.');
        }

        $validated = $request->validate([
            'failure_category' => 'required|in:out_of_stock,over_budget,no_responsive_bids,specification_revision,other',
            'failure_reason'   => 'required|string|min:5|max:1000',
        ], [
            'failure_category.required' => 'Kategori alasan kegagalan tender wajib dipilih.',
            'failure_reason.required'   => 'Penjelasan rinci alasan tender gagal wajib dicantumkan.',
            'failure_reason.min'        => 'Penjelasan alasan minimal 5 karakter.',
        ]);

        $categoryLabels = [
            'out_of_stock'           => 'Stok Tidak Tersedia / Discontinued',
            'over_budget'            => 'Seluruh Penawaran Melampaui Pagu Anggaran',
            'no_responsive_bids'     => 'Tidak Ada Penawaran yang Memenuhi Syarat (Non-Responsive)',
            'specification_revision' => 'Perlu Penyesuaian Spesifikasi Teknis',
            'other'                  => 'Alasan Lainnya',
        ];

        $catLabel = $categoryLabels[$validated['failure_category']] ?? 'Tender Gagal';
        $oldStatus = $purchaseRequest->status;

        DB::transaction(function () use ($purchaseRequest, $validated, $catLabel, $oldStatus, $user) {
            // Deselect any selected quotation
            $purchaseRequest->quotations()->update([
                'is_selected' => false,
            ]);

            // Update PR status back to revision_required so Requester can revise
            $purchaseRequest->update([
                'status' => 'revision_required',
            ]);

            // Log on StatusHistory
            StatusHistory::create([
                'purchase_request_id' => $purchaseRequest->id,
                'from_status'         => $oldStatus,
                'to_status'           => 'revision_required',
                'user_id'             => $user->id,
                'notes'               => "Tender Pengadaan RFQ Dibatalkan oleh Procurement [{$catLabel}]: {$validated['failure_reason']}. Berkas dikembalikan ke pemohon untuk revisi spesifikasi atau penyesuaian pagu anggaran.",
            ]);

            // Record SHA-256 Audit Trail
            AuditTrailService::record(
                'rfq_tender_failed',
                $purchaseRequest,
                $purchaseRequest->pr_number,
                beforeState: ['status' => $oldStatus],
                afterState: [
                    'status' => 'revision_required',
                    'failure_category' => $validated['failure_category'],
                    'failure_reason' => $validated['failure_reason'],
                ],
                description: "{$user->name} membatalkan proses tender RFQ PR {$purchaseRequest->pr_number} [{$catLabel}]. Alasan: {$validated['failure_reason']}"
            );

            // Multi-channel notification to Requester
            InAppNotification::create([
                'user_id' => $purchaseRequest->user_id,
                'title'   => "Tender Pengadaan Gagal: PR #{$purchaseRequest->pr_number}",
                'message' => "Proses tender RFQ untuk '{$purchaseRequest->title}' gagal ({$catLabel}): {$validated['failure_reason']}. Pengajuan dikembalikan ke Anda untuk revisi.",
                'link'    => route('purchase-requests.show', $purchaseRequest),
                'type'    => 'rfq_failed',
            ]);
        });

        return redirect()->route('purchase-requests.show', $purchaseRequest)
            ->with('success', "Proses tender berhasil dibatalkan. Pengajuan PR #{$purchaseRequest->pr_number} telah dikembalikan ke pemohon dengan status revisi.");
    }
}
