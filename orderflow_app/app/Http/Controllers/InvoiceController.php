<?php

namespace App\Http\Controllers;

use App\Models\InAppNotification;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PurchaseOrder;
use App\Models\StatusHistory;
use App\Services\AuditTrailService;
use App\Services\ThreeWayMatchingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    protected ThreeWayMatchingService $matchingService;

    public function __construct(ThreeWayMatchingService $matchingService)
    {
        $this->matchingService = $matchingService;
    }

    /**
     * Display a listing of vendor invoices with 3-Way Match & Payment statuses
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['vendor', 'purchaseOrder', 'verifier', 'creator'])
            ->latest();

        if ($request->filled('matching_status')) {
            $query->where('matching_status', $request->matching_status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('internal_invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('vendor', fn ($vq) => $vq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('purchaseOrder', fn ($poq) => $poq->where('po_number', 'like', "%{$search}%"));
            });
        }

        $invoices = $query->paginate(10)->withQueryString();

        // Optimized single-pass aggregation for metrics (reduces 7 queries to 1)
        $metricsRow = Invoice::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN matching_status = 'matched' THEN 1 ELSE 0 END) as matched,
            SUM(CASE WHEN matching_status IN ('mismatch_quantity', 'mismatch_price') THEN 1 ELSE 0 END) as mismatch,
            SUM(CASE WHEN payment_status = 'unpaid' THEN 1 ELSE 0 END) as unpaid,
            SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) as paid,
            COALESCE(SUM(net_payable_amount), 0) as total_net_payable,
            COALESCE(SUM(paid_amount), 0) as total_paid
        ")->first();

        $metrics = [
            'total' => (int) ($metricsRow->total ?? 0),
            'matched' => (int) ($metricsRow->matched ?? 0),
            'mismatch' => (int) ($metricsRow->mismatch ?? 0),
            'unpaid' => (int) ($metricsRow->unpaid ?? 0),
            'paid' => (int) ($metricsRow->paid ?? 0),
            'total_net_payable' => (float) ($metricsRow->total_net_payable ?? 0),
            'total_paid' => (float) ($metricsRow->total_paid ?? 0),
        ];

        return view('invoices.index', compact('invoices', 'metrics'));
    }

    /**
     * Show form to register a vendor invoice against a Purchase Order
     */
    public function create(Request $request)
    {
        $poId = $request->query('purchase_order_id');
        if (!$poId) {
            return redirect()->route('purchase-orders.index')
                ->with('error', 'Pilih Purchase Order terlebih dahulu untuk mendaftarkan faktur vendor.');
        }

        $purchaseOrder = PurchaseOrder::with(['vendor', 'items', 'goodsReceipts.items'])
            ->findOrFail($poId);

        if (in_array($purchaseOrder->status, ['draft', 'cancelled'])) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'Faktur tidak dapat didaftarkan untuk PO yang berstatus Draf atau Dibatalkan.');
        }

        return view('invoices.create', compact('purchaseOrder'));
    }

    /**
     * Store and run automated 3-Way Matching engine
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'invoice_number' => 'required|string|max:100',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'tax_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.po_item_id' => 'required|exists:po_items,id',
            'items.*.quantity_invoiced' => 'required|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
        ], [
            'invoice_number.required' => 'Nomor Faktur / Invoice dari vendor wajib diisi.',
            'due_date.after_or_equal' => 'Jatuh tempo tidak boleh lebih awal dari tanggal faktur.',
            'items.required' => 'Minimal satu item faktur harus disertakan.',
        ]);

        $purchaseOrder = PurchaseOrder::findOrFail($validated['purchase_order_id']);

        // Execute 3-Way Matching Engine
        $matchResult = $this->matchingService->performMatching(
            $purchaseOrder,
            $validated['items'],
            isset($validated['tax_amount']) ? (float) $validated['tax_amount'] : null
        );

        $user = auth()->user();

        $invoice = DB::transaction(function () use ($purchaseOrder, $validated, $matchResult, $user) {
            $internalNumber = Invoice::generateInternalInvoiceNumber('FIN');

            $inv = Invoice::create([
                'invoice_number' => $validated['invoice_number'],
                'internal_invoice_number' => $internalNumber,
                'purchase_order_id' => $purchaseOrder->id,
                'vendor_id' => $purchaseOrder->vendor_id,
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'subtotal' => $matchResult['subtotal'],
                'tax_amount' => $matchResult['tax_amount'],
                'penalty_deduction' => $matchResult['penalty_deduction'],
                'net_payable_amount' => $matchResult['net_payable_amount'],
                'matching_status' => $matchResult['matching_status'],
                'matching_notes' => $matchResult['matching_notes'],
                'payment_status' => 'unpaid',
                'created_by' => $user->id,
                'verified_by' => ($matchResult['matching_status'] === 'matched') ? $user->id : null,
                'verified_at' => ($matchResult['matching_status'] === 'matched') ? now() : null,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($matchResult['item_results'] as $itemRes) {
                InvoiceItem::create([
                    'invoice_id' => $inv->id,
                    'po_item_id' => $itemRes['po_item_id'],
                    'item_name' => $itemRes['item_name'],
                    'quantity_invoiced' => $itemRes['quantity_invoiced'],
                    'unit_price' => $itemRes['invoiced_unit_price'],
                    'subtotal' => $itemRes['subtotal'],
                ]);
            }

            // Log PR status history if linked
            if ($purchaseOrder->purchase_request_id) {
                StatusHistory::create([
                    'purchase_request_id' => $purchaseOrder->purchase_request_id,
                    'from_status' => $purchaseOrder->status,
                    'to_status' => $purchaseOrder->status,
                    'user_id' => $user->id,
                    'notes' => "Faktur Vendor #{$inv->invoice_number} ({$inv->internal_invoice_number}) didaftarkan. Hasil 3-Way Match: {$inv->matching_status_label}. Total Tagihan Bersih: {$inv->formatted_net_payable_amount}.",
                ]);
            }

            return $inv;
        });

        // Audit Trail
        AuditTrailService::record(
            action: 'invoice_created',
            entity: $invoice,
            entityLabel: $invoice->internal_invoice_number,
            beforeState: null,
            afterState: [
                'matching_status' => $invoice->matching_status,
                'net_payable_amount' => $invoice->net_payable_amount,
                'penalty_deduction' => $invoice->penalty_deduction,
            ],
            description: "{$user->name} mendaftarkan Faktur Vendor {$invoice->invoice_number} ({$invoice->internal_invoice_number}) dengan status 3-Way Match: {$invoice->matching_status_label}.",
            request: $request,
        );

        $flashType = ($invoice->matching_status === 'matched') ? 'success' : 'warning';
        $flashMsg = ($invoice->matching_status === 'matched')
            ? "Faktur {$invoice->internal_invoice_number} berhasil didaftarkan dan Lolos 3-Way Match."
            : "Faktur {$invoice->internal_invoice_number} didaftarkan dengan catatan: {$invoice->matching_status_label}. Pembayaran ditahan hingga diverifikasi.";

        return redirect()->route('invoices.show', $invoice)->with($flashType, $flashMsg);
    }

    /**
     * Show invoice details, 3-Way Matching matrix, and payment history
     */
    public function show(Invoice $invoice)
    {
        $invoice->load([
            'purchaseOrder.items',
            'purchaseOrder.goodsReceipts.items',
            'vendor',
            'items.poItem',
            'verifier',
            'creator',
        ]);

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Record payment disbursement for an invoice (Finance / Admin only)
     */
    public function recordPayment(Request $request, Invoice $invoice)
    {
        $user = auth()->user();
        if (!$user->hasRole(['finance', 'admin'])) {
            abort(403, 'Hanya divisi Keuangan (Finance) atau Administrator yang berwenang mencatat pembayaran.');
        }

        $validated = $request->validate([
            'payment_date' => 'required|date',
            'paid_amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string|max:50',
            'payment_reference' => 'required|string|max:100',
            'notes' => 'nullable|string|max:500',
        ], [
            'payment_reference.required' => 'Nomor referensi / bukti transfer bank wajib diisi.',
            'paid_amount.min' => 'Jumlah pembayaran minimal Rp 1.',
        ]);

        if ($invoice->matching_status !== 'matched' && $invoice->payment_status === 'unpaid') {
            // Check if user forces payment on mismatch or if blocked
            if (!$request->boolean('override_mismatch')) {
                return back()->with('error', 'Pembayaran tidak dapat diproses karena hasil 3-Way Match belum cocok. Centang persetujuan khusus jika ingin override.');
            }
        }

        $newPaidTotal = (float) $invoice->paid_amount + (float) $validated['paid_amount'];
        $netPayable = (float) $invoice->net_payable_amount;

        $newPaymentStatus = ($newPaidTotal >= $netPayable) ? 'paid' : 'partially_paid';

        $beforeState = [
            'payment_status' => $invoice->payment_status,
            'paid_amount' => $invoice->paid_amount,
        ];

        DB::transaction(function () use ($invoice, $validated, $newPaidTotal, $newPaymentStatus, $user) {
            $invoice->update([
                'paid_amount' => $newPaidTotal,
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'payment_reference' => $validated['payment_reference'],
                'payment_status' => $newPaymentStatus,
            ]);

            // Notify Requester & Procurement
            if ($invoice->purchaseOrder && $invoice->purchaseOrder->purchaseRequest) {
                InAppNotification::create([
                    'user_id' => $invoice->purchaseOrder->purchaseRequest->user_id,
                    'title' => "Pembayaran Faktur ({$invoice->internal_invoice_number})",
                    'message' => "Pembayaran faktur vendor {$invoice->vendor?->name} sebesar Rp " . number_format($validated['paid_amount'], 0, ',', '.') . " telah dicatat oleh Finance. Status: {$invoice->payment_status_label}.",
                    'link' => route('invoices.show', $invoice),
                    'type' => 'invoice_payment',
                ]);
            }
        });

        // Audit Trail
        AuditTrailService::record(
            action: 'invoice_payment_recorded',
            entity: $invoice,
            entityLabel: $invoice->internal_invoice_number,
            beforeState: $beforeState,
            afterState: [
                'payment_status' => $invoice->payment_status,
                'paid_amount' => $invoice->paid_amount,
                'payment_reference' => $invoice->payment_reference,
            ],
            description: "{$user->name} mencatat pembayaran faktur {$invoice->internal_invoice_number} sebesar Rp " . number_format($validated['paid_amount'], 0, ',', '.') . " ({$invoice->payment_status_label}).",
            request: $request,
        );

        return redirect()->route('invoices.show', $invoice)
            ->with('success', "Pembayaran faktur sebesar Rp " . number_format($validated['paid_amount'], 0, ',', '.') . " berhasil dicatat. Status: {$invoice->payment_status_label}.");
    }
}
