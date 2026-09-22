<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuotationRequest;
use App\Models\InAppNotification;
use App\Models\PurchaseRequest;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\StatusHistory;
use App\Models\Vendor;
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

        return view('quotations.create', compact('purchaseRequest', 'vendors', 'existingVendorIds'));
    }

    /**
     * Store quotation and line items
     */
    public function store(QuotationRequest $request, PurchaseRequest $purchaseRequest)
    {
        $user = auth()->user();

        $quotation = DB::transaction(function () use ($request, $purchaseRequest, $user) {
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
                $filePath = $request->file('attachment')->store('quotation_files', 'public');
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

        return redirect()->route('quotations.compare', $purchaseRequest)
            ->with('success', "Penawaran dari {$quotation->vendor?->name} berhasil dicatat.");
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
            $messages['single_source_reason.required'] = 'Alasan penetapan Penyedia Tunggal (Single Source) wajib dijelaskan.';
            $messages['single_source_reason.min'] = 'Alasan Single Source minimal 10 karakter.';
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

        DB::transaction(function () use ($purchaseRequest, $selectedQuotation, $request, $isSingleSource, $user) {
            // Deselect previous
            $purchaseRequest->quotations()->update([
                'is_selected' => false,
                'selection_reason' => null,
                'is_single_source' => false,
                'single_source_reason' => null,
            ]);

            // Select chosen quotation
            $selectedQuotation->update([
                'is_selected' => true,
                'selection_reason' => $request->selection_reason,
                'is_single_source' => $isSingleSource,
                'single_source_reason' => $isSingleSource ? $request->single_source_reason : null,
            ]);

            // Update PR status to 'processing'
            $oldStatus = $purchaseRequest->status;
            $purchaseRequest->update(['status' => 'processing']);

            // Record status history audit trail
            $vendorName = $selectedQuotation->vendor->name;
            $notes = "Procurement menetapkan vendor: {$vendorName} (#{$selectedQuotation->quotation_number}) senilai {$selectedQuotation->formatted_grand_total}. Alasan: {$request->selection_reason}";
            if ($isSingleSource) {
                $notes .= " [Single Source: {$request->single_source_reason}]";
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
}
