<?php

namespace App\Http\Controllers;

use App\Models\InAppNotification;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\StatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    /**
     * Display listing of Purchase Orders
     */
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['vendor', 'purchaseRequest.department', 'issuer', 'items'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
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

        $metrics = [
            'total' => PurchaseOrder::count(),
            'issued' => PurchaseOrder::where('status', 'issued')->count(),
            'partially_received' => PurchaseOrder::where('status', 'partially_received')->count(),
            'completed' => PurchaseOrder::where('status', 'completed')->count(),
        ];

        return view('purchase_orders.index', compact('purchaseOrders', 'metrics'));
    }

    /**
     * Show form to issue PO from an approved PR & awarded quotation
     */
    public function create(Request $request)
    {
        $prId = $request->query('purchase_request_id');
        if (!$prId) {
            return redirect()->route('quotations.index')
                ->with('error', 'Pilih pengajuan yang telah memiliki penetapan vendor untuk menerbitkan PO.');
        }

        $purchaseRequest = PurchaseRequest::with(['items', 'quotations.vendor'])->findOrFail($prId);

        // Validation rule: PO cannot be created from PR that is not approved or processing
        if (!in_array($purchaseRequest->status, ['approved', 'processing'])) {
            return redirect()->route('purchase-requests.show', $purchaseRequest)
                ->with('error', 'Purchase Order tidak dapat dibuat dari pengajuan yang belum disetujui penuh.');
        }

        $selectedQuotation = $purchaseRequest->selectedQuotation();
        if (!$selectedQuotation) {
            return redirect()->route('quotations.compare', $purchaseRequest)
                ->with('error', 'Harap tetapkan vendor pemenang terlebih dahulu sebelum menerbitkan Purchase Order.');
        }

        $selectedQuotation->load(['vendor', 'items']);

        // Check if an active PO already exists for this PR
        $existingPo = $purchaseRequest->purchaseOrder();

        return view('purchase_orders.create', compact('purchaseRequest', 'selectedQuotation', 'existingPo'));
    }

    /**
     * Store and issue official PO
     */
    public function store(Request $request)
    {
        $request->validate([
            'purchase_request_id' => ['required', 'exists:purchase_requests,id'],
            'payment_terms' => ['required', 'string', 'max:100'],
            'delivery_target_date' => ['nullable', 'date', 'after_or_equal:today'],
            'status' => ['required', 'in:draft,issued'],
            'notes' => ['nullable', 'string'],
        ], [
            'payment_terms.required' => 'Syarat pembayaran (Terms of Payment) wajib diisi.',
            'delivery_target_date.after_or_equal' => 'Estimasi tanggal pengiriman tidak boleh di masa lalu.',
        ]);

        $purchaseRequest = PurchaseRequest::with(['quotations.items', 'items'])->findOrFail($request->purchase_request_id);

        if (!in_array($purchaseRequest->status, ['approved', 'processing'])) {
            return back()->withErrors(['purchase_request_id' => 'PO tidak dapat dibuat dari PR yang belum disetujui.']);
        }

        $quotation = $purchaseRequest->selectedQuotation();
        if (!$quotation) {
            return back()->withErrors(['purchase_request_id' => 'PO tidak dapat dibuat sebelum ada vendor pemenang yang dipilih.']);
        }

        $quotation->load(['vendor', 'items']);
        $user = auth()->user();

        $po = DB::transaction(function () use ($request, $purchaseRequest, $quotation, $user) {
            $poNumber = PurchaseOrder::generatePoNumber();

            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $poNumber,
                'purchase_request_id' => $purchaseRequest->id,
                'vendor_id' => $quotation->vendor_id,
                'quotation_id' => $quotation->id,
                'issued_by' => $user->id,
                'order_date' => now()->toDateString(),
                'delivery_target_date' => $request->delivery_target_date,
                'subtotal' => $quotation->subtotal,
                'shipping_fee' => $quotation->shipping_cost,
                'tax_amount' => $quotation->tax_amount,
                'grand_total' => $quotation->grand_total,
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
}
