<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\InAppNotification;
use App\Models\PoItem;
use App\Models\PurchaseOrder;
use App\Models\StatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoodsReceiptController extends Controller
{
    /**
     * Show form to record Goods Receipt or Service Acceptance (BAST)
     */
    public function create(Request $request)
    {
        $poId = $request->query('purchase_order_id');
        if (!$poId) {
            return redirect()->route('purchase-orders.index')
                ->with('error', 'Pilih Purchase Order yang ingin dicatat penerimaan barang/jasanya.');
        }

        $purchaseOrder = PurchaseOrder::with(['items', 'vendor', 'purchaseRequest'])->findOrFail($poId);

        if ($purchaseOrder->status === 'completed') {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'Seluruh barang/jasa pada PO ini telah 100% diterima lengkap.');
        }

        if ($purchaseOrder->status === 'cancelled') {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'PO ini berstatus dibatalkan dan tidak dapat menerima barang.');
        }

        return view('goods_receipts.create', compact('purchaseOrder'));
    }

    /**
     * Store Goods Receipt or BAST
     */
    public function store(Request $request)
    {
        $request->validate([
            'purchase_order_id' => ['required', 'exists:purchase_orders,id'],
            'receipt_type' => ['required', 'in:goods,service'],
            'received_date' => ['required', 'date', 'before_or_equal:today'],
            'item_condition' => ['required', 'string', 'max:100'],
            'delivery_note_no' => ['nullable', 'string', 'max:100'],
            'inspection_notes' => ['nullable', 'string'],
            'delivery_note_doc' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'bast_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.po_item_id' => ['required', 'exists:po_items,id'],
            'items.*.quantity_received' => ['required', 'integer', 'min:0'],
            'items.*.quantity_rejected' => ['nullable', 'integer', 'min:0'],
        ], [
            'received_date.before_or_equal' => 'Tanggal penerimaan fisik tidak boleh di masa depan.',
            'items.required' => 'Daftar item penerimaan wajib disertakan.',
        ]);

        $purchaseOrder = PurchaseOrder::with('items')->findOrFail($request->purchase_order_id);

        // Validate quantities received do not exceed remaining quantity
        $totalReceivedInThisBatch = 0;
        $itemsPayload = [];

        foreach ($request->items as $itemData) {
            $poItem = $purchaseOrder->items->firstWhere('id', $itemData['po_item_id']);
            if (!$poItem) {
                continue;
            }

            $qtyReceived = (int) $itemData['quantity_received'];
            $qtyRejected = (int) ($itemData['quantity_rejected'] ?? 0);
            $remaining = $poItem->quantity - $poItem->received_quantity;

            if ($qtyReceived > $remaining) {
                return back()->withErrors([
                    'items' => "Jumlah diterima untuk '{$poItem->item_name}' ({$qtyReceived} unit) melebihi sisa pesanan yang belum datang ({$remaining} unit)."
                ])->withInput();
            }

            $totalReceivedInThisBatch += $qtyReceived;
            $itemsPayload[] = [
                'po_item' => $poItem,
                'quantity_received' => $qtyReceived,
                'quantity_rejected' => $qtyRejected,
                'notes' => $itemData['notes'] ?? null,
            ];
        }

        if ($totalReceivedInThisBatch <= 0) {
            return back()->withErrors([
                'items' => 'Minimal harus ada 1 barang atau jasa dengan jumlah kuantitas diterima lebih dari 0 unit.'
            ])->withInput();
        }

        $user = auth()->user();

        $goodsReceipt = DB::transaction(function () use ($request, $purchaseOrder, $itemsPayload, $user) {
            $receiptType = $request->receipt_type;
            $grNumber = GoodsReceipt::generateGrNumber($receiptType);

            // Handle file uploads
            $deliveryDocPath = null;
            if ($request->hasFile('delivery_note_doc')) {
                $deliveryDocPath = $request->file('delivery_note_doc')->store('receipt_docs', 'public');
            }

            $bastDocPath = null;
            if ($request->hasFile('bast_document')) {
                $bastDocPath = $request->file('bast_document')->store('bast_docs', 'public');
            }

            $gr = GoodsReceipt::create([
                'gr_number' => $grNumber,
                'purchase_order_id' => $purchaseOrder->id,
                'received_by' => $user->id,
                'receipt_type' => $receiptType,
                'received_date' => $request->received_date,
                'delivery_note_no' => $request->delivery_note_no,
                'delivery_note_doc' => $deliveryDocPath,
                'item_condition' => $request->item_condition,
                'inspection_notes' => $request->inspection_notes,
                'status' => 'partially_received', // will be updated by recalculateStatus
                'service_period_start' => $request->service_period_start,
                'service_period_end' => $request->service_period_end,
                'service_deliverables' => $request->service_deliverables,
                'acceptance_approver_name' => $request->acceptance_approver_name,
                'bast_document_path' => $bastDocPath,
            ]);

            // Save items and update po_items received_quantity
            foreach ($itemsPayload as $itemInfo) {
                $poItem = $itemInfo['po_item'];
                $qtyReceived = $itemInfo['quantity_received'];
                $qtyRejected = $itemInfo['quantity_rejected'];

                $gr->items()->create([
                    'po_item_id' => $poItem->id,
                    'quantity_received' => $qtyReceived,
                    'quantity_rejected' => $qtyRejected,
                    'notes' => $itemInfo['notes'],
                ]);

                // Increment po_item received_quantity
                $poItem->increment('received_quantity', $qtyReceived);
            }

            // Recalculate PO status automatically (issued -> partially_received -> completed)
            $purchaseOrder->recalculateStatus();
            $purchaseOrder->refresh();

            // Sync GR status
            $gr->update([
                'status' => ($purchaseOrder->status === 'completed') ? 'completed' : 'partially_received',
            ]);

            // Record status history audit
            $label = ($receiptType === 'service') ? 'Berita Acara Serah Terima (BAST)' : 'Tanda Terima Barang (Goods Receipt)';
            StatusHistory::create([
                'purchase_request_id' => $purchaseOrder->purchase_request_id,
                'from_status' => $purchaseOrder->purchaseRequest?->status,
                'to_status' => ($purchaseOrder->status === 'completed') ? 'completed' : 'processing',
                'user_id' => $user->id,
                'notes' => "{$label} #{$grNumber} dicatat untuk PO #{$purchaseOrder->po_number}. Status PO saat ini: {$purchaseOrder->status_label}.",
            ]);

            // Notify Requester
            InAppNotification::create([
                'user_id' => $purchaseOrder->purchaseRequest?->user_id,
                'title' => "Penerimaan Barang/Jasa (#{$grNumber})",
                'message' => "Barang pesanan Anda untuk '{$purchaseOrder->purchaseRequest?->title}' telah diterima di kantor oleh tim logistik.",
                'link' => route('purchase-orders.show', $purchaseOrder),
                'type' => 'goods_received',
            ]);

            return $gr;
        });

        $successMsg = ($request->receipt_type === 'service')
            ? "Berita Acara Serah Terima (BAST) #{$goodsReceipt->gr_number} berhasil dicatat."
            : "Tanda Terima Barang (Goods Receipt) #{$goodsReceipt->gr_number} berhasil dicatat.";

        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', $successMsg);
    }

    /**
     * Show detail of Goods Receipt / BAST
     */
    public function show(GoodsReceipt $goodsReceipt)
    {
        $goodsReceipt->load([
            'purchaseOrder.vendor',
            'purchaseOrder.purchaseRequest.department',
            'receiver',
            'items.poItem',
        ]);

        return view('goods_receipts.show', compact('goodsReceipt'));
    }
}
