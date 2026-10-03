<?php

namespace App\Http\Controllers;

use App\Models\InAppNotification;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Quotation;
use App\Models\StatusHistory;
use App\Services\AuditTrailService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    /**
     * Display listing of Purchase Orders
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = PurchaseOrder::with(['vendor', 'purchaseRequest.department', 'issuer', 'items'])
            ->latest();

        if ($user && $user->hasRole('requester')) {
            $query->whereHas('purchaseRequest', fn ($pq) => $pq->where('user_id', $user->id));
        }

        if ($request->filled('status')) {
            if ($request->status === 'overdue') {
                $query->overdue();
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhereHas('vendor', fn ($vq) => $vq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('purchaseRequest', fn ($pq) => $pq->where('pr_number', 'like', "%{$search}%")->orWhere('title', 'like', "%{$search}%"));
            });
        }

        $purchaseOrders = $query->paginate(10)->withQueryString();

        $todayStr = Carbon::today()->toDateString();
        $metricsRow = PurchaseOrder::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'issued' THEN 1 ELSE 0 END) as issued,
            SUM(CASE WHEN status = 'partially_received' THEN 1 ELSE 0 END) as partially_received,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status IN ('issued', 'partially_received') AND delivery_target_date < ? THEN 1 ELSE 0 END) as overdue
        ", [$todayStr])->first();

        $metrics = [
            'total' => (int) ($metricsRow->total ?? 0),
            'issued' => (int) ($metricsRow->issued ?? 0),
            'partially_received' => (int) ($metricsRow->partially_received ?? 0),
            'completed' => (int) ($metricsRow->completed ?? 0),
            'overdue' => (int) ($metricsRow->overdue ?? 0),
        ];

        return view('purchase_orders.index', compact('purchaseOrders', 'metrics'));
    }

    /**
     * Show form to issue PO from an approved PR & awarded quotation
     */
    public function create(Request $request)
    {
        $prId = $request->query('purchase_request_id');
        $quotationId = $request->query('quotation_id');

        if (!$prId) {
            return redirect()->route('quotations.index')
                ->with('error', 'Pilih pengajuan yang telah memiliki penetapan vendor untuk menerbitkan PO.');
        }

        $purchaseRequest = PurchaseRequest::with(['items', 'quotations.vendor', 'purchaseOrders.vendor'])->findOrFail($prId);

        // Validation rule: PO cannot be created from PR that is not approved or processing
        if (!in_array($purchaseRequest->status, ['approved', 'processing'])) {
            return redirect()->route('purchase-requests.show', $purchaseRequest)
                ->with('error', 'Purchase Order tidak dapat dibuat dari pengajuan yang belum disetujui penuh.');
        }

        // Multi-Vendor Support: Select specific quotation if requested, or default to first unissued
        if ($quotationId) {
            $selectedQuotation = Quotation::where('purchase_request_id', $purchaseRequest->id)
                ->where('id', $quotationId)
                ->firstOrFail();
        } else {
            $unissued = $purchaseRequest->unissuedQuotations();
            $selectedQuotation = $unissued->first() ?? $purchaseRequest->selectedQuotation();
        }

        if (!$selectedQuotation) {
            return redirect()->route('quotations.compare', $purchaseRequest)
                ->with('error', 'Harap tetapkan minimal satu vendor pemenang terlebih dahulu sebelum menerbitkan Purchase Order.');
        }

        $selectedQuotation->load(['vendor', 'items']);

        // Check all existing POs for this PR
        $existingPos = $purchaseRequest->purchaseOrders()->with('vendor')->get();
        $existingPo = $existingPos->last();

        // Awarded quotations for multi-vendor navigation
        $selectedQuotations = $purchaseRequest->selectedQuotations();

        return view('purchase_orders.create', compact('purchaseRequest', 'selectedQuotation', 'selectedQuotations', 'existingPo', 'existingPos'));
    }

    /**
     * Store and issue official PO
     */
    public function store(Request $request)
    {
        $request->validate([
            'purchase_request_id' => ['required', 'exists:purchase_requests,id'],
            'quotation_id' => ['nullable', 'exists:quotations,id'],
            'payment_terms' => ['required', 'string', 'max:100'],
            'delivery_target_date' => ['nullable', 'date', 'after_or_equal:today'],
            'status' => ['required', 'in:draft,issued'],
            'notes' => ['nullable', 'string'],
        ], [
            'payment_terms.required' => 'Syarat pembayaran (Terms of Payment) wajib diisi.',
            'delivery_target_date.after_or_equal' => 'Estimasi tanggal pengiriman tidak boleh di masa lalu.',
        ]);

        $purchaseRequest = PurchaseRequest::with(['quotations.items', 'items', 'purchaseOrders'])->findOrFail($request->purchase_request_id);

        if (!in_array($purchaseRequest->status, ['approved', 'processing'])) {
            return back()->withErrors(['purchase_request_id' => 'PO tidak dapat dibuat dari PR yang belum disetujui.']);
        }

        // Identify quotation to issue PO for
        $quotation = null;
        if ($request->filled('quotation_id')) {
            $quotation = Quotation::where('purchase_request_id', $purchaseRequest->id)
                ->where('id', $request->quotation_id)
                ->first();
        }
        if (!$quotation) {
            $quotation = $purchaseRequest->selectedQuotation();
        }

        if (!$quotation) {
            return back()->withErrors(['purchase_request_id' => 'PO tidak dapat dibuat sebelum ada vendor pemenang yang dipilih.']);
        }

        // Prevent duplicate PO for the exact same quotation
        $alreadyIssued = PurchaseOrder::where('purchase_request_id', $purchaseRequest->id)
            ->where('quotation_id', $quotation->id)
            ->where('status', '!=', 'cancelled')
            ->first();

        if ($alreadyIssued) {
            return back()->withErrors([
                'purchase_request_id' => "Purchase Order untuk penawaran vendor {$quotation->vendor?->name} sudah pernah diterbitkan dengan nomor #{$alreadyIssued->po_number}."
            ]);
        }

        // Corporate Governance Rule: Budget Variance & Re-Approval Policy
        // If the winning vendor quotation exceeds the approved PR estimate by more than 10%,
        // re-approval from manager/finance is required before PO can be issued.
        $toleranceRate = 1.10; // 10% budget tolerance threshold
        if ($purchaseRequest->estimated_total > 0 && $quotation->grand_total > ($purchaseRequest->estimated_total * $toleranceRate)) {
            $formattedQuotation = 'Rp ' . number_format($quotation->grand_total, 0, ',', '.');
            $formattedEstimate = 'Rp ' . number_format($purchaseRequest->estimated_total, 0, ',', '.');
            $variancePercent = round((($quotation->grand_total - $purchaseRequest->estimated_total) / $purchaseRequest->estimated_total) * 100, 1);

            return back()->withErrors([
                'purchase_request_id' => "Peringatan Tata Kelola Anggaran: Penawaran vendor ({$formattedQuotation}) melebihi estimasi pagu PR ({$formattedEstimate}) sebesar +{$variancePercent}%. Batas toleransi 10% terlampaui. Wajib mengajukan persetujuan ulang (Re-Approval) pagu anggaran sebelum PO dapat diterbitkan."
            ]);
        }

        $quotation->load(['vendor', 'items']);
        $user = auth()->user();

        // Tax calculation & rounding tolerance engine (PPN 11% / 12% & Header vs Line-item)
        $taxRate = (float) $request->input('tax_rate', 11.00);
        $taxMode = $request->input('tax_calculation_mode', 'line_item');
        $taxRoundingTolerance = 100.00; // max Rp 100 corporate tolerance

        $subtotal = (float) $quotation->subtotal;
        $shippingFee = (float) $quotation->shipping_cost;

        if ($taxMode === 'header_subtotal') {
            $theoreticalTax = round($subtotal * ($taxRate / 100.0), 2);
        } else {
            // Line-item calculation: sum of item taxes
            $theoreticalTax = 0.0;
            foreach ($quotation->items as $item) {
                $theoreticalTax += round(((float) $item->subtotal) * ($taxRate / 100.0), 2);
            }
        }

        // Determine actual tax amount and validate rounding difference
        if ($request->filled('tax_amount')) {
            $chosenTax = (float) $request->input('tax_amount');
            $taxDiff = abs($chosenTax - $theoreticalTax);
            if ($taxDiff > $taxRoundingTolerance) {
                return back()->withInput()->withErrors([
                    'tax_amount' => "Selisih pembulatan nilai PPN (Rp " . number_format($taxDiff, 0, ',', '.') . ") melebihi batas toleransi pembulatan korporat (Maks. Rp 100). Mohon verifikasi tarif pajak atau harga satuan item."
                ]);
            }
            $taxRoundingDifference = round($chosenTax - $theoreticalTax, 2);
        } else {
            $chosenTax = $theoreticalTax;
            $taxRoundingDifference = 0.0;
        }
        $grandTotal = $subtotal + $shippingFee + $chosenTax;
        $overDeliveryTolerance = (float) $request->input('over_delivery_tolerance_percentage', 5.00);

        $po = DB::transaction(function () use ($request, $purchaseRequest, $quotation, $user, $subtotal, $shippingFee, $chosenTax, $taxRate, $taxMode, $taxRoundingTolerance, $taxRoundingDifference, $grandTotal, $overDeliveryTolerance) {
            $poNumber = PurchaseOrder::generatePoNumber();
            $isMultiPo = $purchaseRequest->purchaseOrders()->count() > 0;

            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $poNumber,
                'purchase_request_id' => $purchaseRequest->id,
                'vendor_id' => $quotation->vendor_id,
                'quotation_id' => $quotation->id,
                'issued_by' => $user->id,
                'order_date' => now()->toDateString(),
                'delivery_target_date' => $request->delivery_target_date,
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_calculation_mode' => $taxMode,
                'tax_rounding_tolerance' => $taxRoundingTolerance,
                'shipping_fee' => $shippingFee,
                'tax_amount' => $chosenTax,
                'tax_rounding_difference' => $taxRoundingDifference,
                'grand_total' => $grandTotal,
                'over_delivery_tolerance_percentage' => $overDeliveryTolerance,
                'payment_terms' => $request->payment_terms,
                'status' => $request->status,
                'notes' => $request->notes,
            ]);

            // Copy items from selected quotation (or PR items)
            foreach ($quotation->items as $qItem) {
                $purchaseOrder->items()->create([
                    'purchase_request_item_id' => $qItem->purchase_request_item_id,
                    'item_name' => $qItem->item_name,
                    'specification' => $qItem->specification,
                    'quantity' => $qItem->quantity,
                    'unit' => $qItem->unit,
                    'unit_price' => $qItem->unit_price,
                    'subtotal' => $qItem->subtotal,
                    'received_quantity' => 0,
                    'over_delivery_tolerance_percentage' => $overDeliveryTolerance,
                    'over_delivered_quantity' => 0,
                ]);
            }

            // Ensure PR status is processing
            $purchaseRequest->update(['status' => 'processing']);

            // Record status history audit
            StatusHistory::create([
                'purchase_request_id' => $purchaseRequest->id,
                'from_status' => $purchaseRequest->status,
                'to_status' => 'processing',
                'user_id' => $user->id,
                'notes' => "Surat Pesanan Resmi (Purchase Order) #{$poNumber} berhasil diterbitkan kepada {$quotation->vendor?->name}.",
            ]);

            // Notify Requester
            InAppNotification::create([
                'user_id' => $purchaseRequest->user_id,
                'title' => "Purchase Order Diterbitkan (#{$poNumber})",
                'message' => "PO resmi untuk pengadaan '{$purchaseRequest->title}' telah diterbitkan kepada {$quotation->vendor?->name}.",
                'link' => route('purchase-orders.show', $purchaseOrder),
                'type' => 'po_issued',
            ]);

            return $purchaseOrder;
        });

        // Audit Trail
        AuditTrailService::record(
            action: 'po_created',
            entity: $po,
            entityLabel: $po->po_number,
            beforeState: null,
            afterState: ['status' => $po->status, 'grand_total' => $po->grand_total],
            description: "{$user->name} menerbitkan Purchase Order {$po->po_number} kepada vendor {$po->vendor?->name}.",
            request: $request,
        );

        return redirect()->route('purchase-orders.show', $po)
            ->with('success', "Surat Pesanan Resmi #{$po->po_number} berhasil diterbitkan.");
    }

    /**
     * Show PO details, item progress, and Goods Receipts
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'purchaseRequest.department',
            'purchaseRequest.user',
            'vendor',
            'issuer',
            'items',
            'goodsReceipts.receiver',
            'goodsReceipts.items',
            'invoices',
        ]);

        return view('purchase_orders.show', compact('purchaseOrder'));
    }

    /**
     * Printable formal corporate PDF view
     */
    public function printPdf(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'purchaseRequest.department',
            'vendor',
            'issuer',
            'items',
        ]);

        return view('purchase_orders.print', compact('purchaseOrder'));
    }

    /**
     * Delete PO (forbidden if Goods Receipts already exist)
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->goodsReceipts()->exists()) {
            return back()->with('error', 'Purchase Order tidak dapat dihapus karena sudah memiliki rekaman penerimaan barang/jasa.');
        }

        $poNumber = $purchaseOrder->po_number;
        $purchaseOrder->items()->delete();
        $purchaseOrder->delete();

        return redirect()->route('purchase-orders.index')
            ->with('success', "Purchase Order #{$poNumber} berhasil dihapus.");
    }

    /**
     * Issue an official default notice (Surat Teguran Wanprestasi) to vendor
     */
    public function sendDefaultNotice(Request $request, PurchaseOrder $purchaseOrder)
    {
        $user = auth()->user();
        if (!$user->hasRole(['procurement', 'admin'])) {
            abort(403, 'Hanya tim Procurement atau Administrator yang berwenang menerbitkan Surat Teguran.');
        }

        if ($purchaseOrder->status === 'completed' || $purchaseOrder->status === 'cancelled') {
            return back()->with('error', 'Surat teguran tidak dapat dikirimkan pada PO yang sudah selesai atau dibatalkan.');
        }

        $purchaseOrder->increment('default_notice_count');
        $purchaseOrder->update([
            'default_notice_sent_at' => now(),
        ]);

        // Record on StatusHistory for PR
        if ($purchaseOrder->purchase_request_id) {
            StatusHistory::create([
                'purchase_request_id' => $purchaseOrder->purchase_request_id,
                'from_status' => $purchaseOrder->status,
                'to_status' => $purchaseOrder->status,
                'user_id' => $user->id,
                'notes' => "Surat Teguran Wanprestasi (Default Notice #{$purchaseOrder->default_notice_count}) diterbitkan kepada {$purchaseOrder->vendor?->name} untuk PO #{$purchaseOrder->po_number}. Keterlambatan {$purchaseOrder->overdue_days} hari kalender. Estimasi denda pinalti keterlambatan: {$purchaseOrder->formatted_estimated_penalty} ({$purchaseOrder->penalty_percentage}%).",
            ]);

            // Notify Requester
            if ($purchaseOrder->purchaseRequest && $purchaseOrder->purchaseRequest->user_id) {
                InAppNotification::create([
                    'user_id' => $purchaseOrder->purchaseRequest->user_id,
                    'title' => "Surat Teguran PO #{$purchaseOrder->po_number}",
                    'message' => "Surat teguran wanprestasi ke-{$purchaseOrder->default_notice_count} telah diterbitkan kepada {$purchaseOrder->vendor?->name} atas keterlambatan pengiriman PO #{$purchaseOrder->po_number}.",
                    'link' => route('purchase-orders.show', $purchaseOrder),
                    'type' => 'po_default_notice',
                ]);
            }
        }

        // Audit Trail
        AuditTrailService::record(
            action: 'po_default_notice_sent',
            entity: $purchaseOrder,
            entityLabel: $purchaseOrder->po_number,
            beforeState: ['default_notice_count' => $purchaseOrder->default_notice_count - 1],
            afterState: [
                'default_notice_count' => $purchaseOrder->default_notice_count,
                'overdue_days' => $purchaseOrder->overdue_days,
                'penalty_percentage' => $purchaseOrder->penalty_percentage,
                'estimated_penalty' => $purchaseOrder->estimated_penalty_amount,
            ],
            description: "{$user->name} menerbitkan Surat Teguran Wanprestasi ke-{$purchaseOrder->default_notice_count} kepada {$purchaseOrder->vendor?->name} untuk PO {$purchaseOrder->po_number}.",
            request: $request,
        );

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', "Surat Teguran Wanprestasi (Default Notice #{$purchaseOrder->default_notice_count}) berhasil diterbitkan kepada vendor {$purchaseOrder->vendor?->name}.");
    }

    /**
     * Cancel PO due to vendor default (wanprestasi) / breach of contract
     */
    public function cancel(Request $request, PurchaseOrder $purchaseOrder)
    {
        $user = auth()->user();
        if (!$user->hasRole(['procurement', 'admin'])) {
            abort(403, 'Hanya tim Procurement atau Administrator yang berwenang membatalkan PO.');
        }

        $validated = $request->validate([
            'cancellation_reason' => 'required|string|min:5|max:1000',
        ], [
            'cancellation_reason.required' => 'Alasan pembatalan PO wajib diisi secara rinci.',
            'cancellation_reason.min' => 'Alasan pembatalan minimal 5 karakter.',
        ]);

        if ($purchaseOrder->status === 'completed') {
            return back()->with('error', 'Purchase Order yang sudah berstatus selesai tidak dapat dibatalkan.');
        }

        if ($purchaseOrder->status === 'cancelled') {
            return back()->with('error', 'Purchase Order ini sudah dibatalkan sebelumnya.');
        }

        $beforeState = ['status' => $purchaseOrder->status];

        DB::transaction(function () use ($purchaseOrder, $validated, $user) {
            $purchaseOrder->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $validated['cancellation_reason'],
            ]);

            // Release quotation selection so procurement can select runner-up without full re-approval
            if ($purchaseOrder->quotation) {
                $purchaseOrder->quotation->update([
                    'is_selected' => false,
                    'selection_reason' => 'Penetapan dibatalkan akibat wanprestasi PO #' . $purchaseOrder->po_number . ': ' . $validated['cancellation_reason'],
                ]);
            }

            // Recalculate parent PR status
            if ($purchaseOrder->purchaseRequest) {
                $purchaseOrder->purchaseRequest->recalculateStatus();

                // Log StatusHistory on PR
                StatusHistory::create([
                    'purchase_request_id' => $purchaseOrder->purchaseRequest->id,
                    'from_status' => $purchaseOrder->purchaseRequest->status,
                    'to_status' => $purchaseOrder->purchaseRequest->status,
                    'user_id' => $user->id,
                    'notes' => "Purchase Order #{$purchaseOrder->po_number} dibatalkan karena wanprestasi vendor {$purchaseOrder->vendor?->name}. Alasan: {$validated['cancellation_reason']}. Alokasi penawaran dibebaskan kembali untuk penetapan vendor alternatif.",
                ]);

                // Notify Requester
                if ($purchaseOrder->purchaseRequest->user_id) {
                    InAppNotification::create([
                        'user_id' => $purchaseOrder->purchaseRequest->user_id,
                        'title' => "PO #{$purchaseOrder->po_number} Dibatalkan",
                        'message' => "PO #{$purchaseOrder->po_number} telah dibatalkan karena wanprestasi vendor ({$purchaseOrder->vendor?->name}). Tim Procurement dapat memilih vendor pengganti.",
                        'link' => route('purchase-orders.show', $purchaseOrder),
                        'type' => 'po_cancelled',
                    ]);
                }
            }
        });

        // Audit Trail
        AuditTrailService::record(
            action: 'po_cancelled_default',
            entity: $purchaseOrder,
            entityLabel: $purchaseOrder->po_number,
            beforeState: $beforeState,
            afterState: [
                'status' => 'cancelled',
                'cancelled_at' => now()->toIso8601String(),
                'cancellation_reason' => $validated['cancellation_reason'],
            ],
            description: "{$user->name} membatalkan Purchase Order {$purchaseOrder->po_number} karena wanprestasi vendor {$purchaseOrder->vendor?->name}.",
            request: $request,
        );

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', "Purchase Order #{$purchaseOrder->po_number} berhasil dibatalkan karena wanprestasi. Alokasi PR telah dibuka kembali.");
    }

    /**
     * Force Close / Short Close PO on partial delivery (when remaining unfulfilled items will never arrive)
     */
    public function shortClose(Request $request, PurchaseOrder $purchaseOrder)
    {
        $user = auth()->user();
        if (! $user->hasRole(['procurement', 'admin'])) {
            abort(403, 'Hanya tim Procurement atau Administrator yang berwenang melakukan Short Close PO.');
        }

        $validated = $request->validate([
            'short_close_reason' => 'required|string|min:5|max:1000',
        ], [
            'short_close_reason.required' => 'Alasan penutupan paksa (Short Close) wajib diisi secara rinci.',
            'short_close_reason.min' => 'Alasan penutupan minimal 5 karakter.',
        ]);

        if ($purchaseOrder->status !== 'partially_received') {
            return back()->with('error', 'Short Close hanya dapat dilakukan pada Purchase Order yang berstatus Penerimaan Sebagian (Partially Received).');
        }

        $beforeState = ['status' => $purchaseOrder->status];

        DB::transaction(function () use ($purchaseOrder, $validated, $user) {
            $purchaseOrder->update([
                'status'             => 'completed',
                'is_short_closed'    => true,
                'short_closed_at'    => now(),
                'short_closed_by'    => $user->id,
                'short_close_reason' => $validated['short_close_reason'],
            ]);

            // Sync parent PR status
            if ($purchaseOrder->purchaseRequest) {
                $purchaseOrder->purchaseRequest->recalculateStatus();

                // Log StatusHistory on PR
                StatusHistory::create([
                    'purchase_request_id' => $purchaseOrder->purchaseRequest->id,
                    'from_status'         => 'processing',
                    'to_status'           => $purchaseOrder->purchaseRequest->status,
                    'user_id'             => $user->id,
                    'notes'               => "Purchase Order #{$purchaseOrder->po_number} ditutup paksa (Short Close) sesuai realisasi fisik yang telah diterima. Sisa pesanan dibatalkan resmi. Alasan: {$validated['short_close_reason']}",
                ]);

                // Notify Requester
                if ($purchaseOrder->purchaseRequest->user_id) {
                    InAppNotification::create([
                        'user_id' => $purchaseOrder->purchaseRequest->user_id,
                        'title'   => "PO #{$purchaseOrder->po_number} Selesai (Short Close)",
                        'message' => "PO #{$purchaseOrder->po_number} telah ditutup sesuai barang yang sudah diterima. Sisa item dibatalkan resmi. Alasan: {$validated['short_close_reason']}",
                        'link'    => route('purchase-orders.show', $purchaseOrder),
                        'type'    => 'po_short_closed',
                    ]);
                }
            }

            // Notify Finance Officers
            $financeUsers = \App\Models\User::where('role', 'finance')->get();
            foreach ($financeUsers as $finUser) {
                InAppNotification::create([
                    'user_id' => $finUser->id,
                    'title'   => "Short Close PO #{$purchaseOrder->po_number}",
                    'message' => "PO #{$purchaseOrder->po_number} telah ditutup paksa. Nilai tagihan yang dapat dibayar hanya atas fisik barang yang sudah diterima.",
                    'link'    => route('purchase-orders.show', $purchaseOrder),
                    'type'    => 'po_short_closed',
                ]);
            }
        });

        // SHA-256 Audit Trail
        AuditTrailService::record(
            action: 'po_short_closed',
            entity: $purchaseOrder,
            entityLabel: $purchaseOrder->po_number,
            beforeState: $beforeState,
            afterState: [
                'status'             => 'completed',
                'is_short_closed'    => true,
                'short_closed_at'    => now()->toIso8601String(),
                'short_closed_by'    => $user->name,
                'short_close_reason' => $validated['short_close_reason'],
            ],
            description: "{$user->name} melakukan Short Close pada PO {$purchaseOrder->po_number}. Sisa pesanan dibatalkan resmi. Alasan: {$validated['short_close_reason']}",
            request: $request,
        );

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', "Purchase Order #{$purchaseOrder->po_number} berhasil ditutup resmi (Short Close). Sisa kuantitas dibatalkan.");
    }
}
