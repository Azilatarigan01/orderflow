<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\InAppNotification;
use App\Models\PoItem;
use App\Models\PurchaseOrder;
use App\Models\StatusHistory;
use App\Services\AuditTrailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

        $uomOptionsPerItem = [];
        foreach ($purchaseOrder->items as $item) {
            $uomOptionsPerItem[$item->id] = \App\Services\UnitConversionService::getConversionOptions($item->unit);
        }

        $serviceStats = $purchaseOrder->goodsReceipts()
            ->where('receipt_type', 'service')
            ->selectRaw('COALESCE(SUM(progress_percentage), 0) as total_progress, COUNT(*) as termin_count')
            ->first();

        $previousServiceProgress = (float) ($serviceStats->total_progress ?? 0);
        $remainingServiceProgress = max(0, round(100 - $previousServiceProgress, 2));
        $nextTerminCount = ((int) ($serviceStats->termin_count ?? 0)) + 1;
        $suggestedTerminName = "Termin {$nextTerminCount}";

        return view('goods_receipts.create', compact(
            'purchaseOrder',
            'uomOptionsPerItem',
            'previousServiceProgress',
            'remainingServiceProgress',
            'suggestedTerminName'
        ));
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
            'termin_name' => ['nullable', 'string', 'max:100'],
            'progress_percentage' => ['nullable', 'numeric', 'min:0.01', 'max:100'],
            'nominal_claimed' => ['nullable', 'numeric', 'min:0'],
            'acceptance_approver_name' => ['nullable', 'string', 'max:150'],
            'service_deliverables' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.po_item_id' => ['required', 'exists:po_items,id'],
            'items.*.received_unit' => ['nullable', 'string', 'max:30'],
            'items.*.raw_quantity_received' => ['nullable', 'numeric', 'min:0'],
            'items.*.raw_quantity_rejected' => ['nullable', 'numeric', 'min:0'],
            'items.*.quantity_received' => ['nullable', 'numeric', 'min:0'],
            'items.*.quantity_rejected' => ['nullable', 'numeric', 'min:0'],
        ], [
            'received_date.before_or_equal' => 'Tanggal penerimaan fisik tidak boleh di masa depan.',
            'items.required' => 'Daftar item penerimaan wajib disertakan.',
        ]);

        $purchaseOrder = PurchaseOrder::with(['items', 'goodsReceipts'])->findOrFail($request->purchase_order_id);

        // Specific validation for Service Acceptance BAST
        if ($request->receipt_type === 'service') {
            $previousProgress = (float) $purchaseOrder->goodsReceipts
                ->where('receipt_type', 'service')
                ->sum('progress_percentage');

            if ($request->filled('progress_percentage')) {
                $requestedProgress = (float) $request->progress_percentage;
                if (($previousProgress + $requestedProgress) > 100.01) {
                    $maxAllowed = max(0, round(100 - $previousProgress, 2));
                    return back()->withErrors([
                        'progress_percentage' => "Akumulasi progres termin jasa melampaui 100% (Sebelumnya: {$previousProgress}%, Input termin ini: {$requestedProgress}%). Maksimal progres yang dapat diinput adalah {$maxAllowed}%."
                    ])->withInput();
                }
            }
        }

        // Validate quantities received do not exceed remaining quantity
        $totalReceivedInThisBatch = 0;
        $itemsPayload = [];

        foreach ($request->items as $itemData) {
            $poItem = $purchaseOrder->items->firstWhere('id', $itemData['po_item_id']);
            if (!$poItem) {
                continue;
            }

            $rawQtyReceived = (float) ($itemData['raw_quantity_received'] ?? $itemData['quantity_received'] ?? 0);
            $rawQtyRejected = (float) ($itemData['raw_quantity_rejected'] ?? $itemData['quantity_rejected'] ?? 0);
            $receivedUnit = $itemData['received_unit'] ?? $poItem->unit;

            // Perform UoM conversion to PO Item unit
            try {
                $conversionReceived = \App\Services\UnitConversionService::convert($rawQtyReceived, $receivedUnit, $poItem->unit);
                $conversionRejected = \App\Services\UnitConversionService::convert($rawQtyRejected, $receivedUnit, $poItem->unit);
            } catch (\InvalidArgumentException $e) {
                return back()->withErrors(['items' => $e->getMessage()])->withInput();
            }

            $qtyReceived = (int) round($conversionReceived['converted_quantity']);
            $qtyRejected = (int) round($conversionRejected['converted_quantity']);
            $conversionMultiplier = $conversionReceived['multiplier'];
            $remaining = $poItem->quantity - $poItem->received_quantity;
            $tolerancePercent = (float) ($poItem->over_delivery_tolerance_percentage ?? 5.0);
            $maxOverDeliveryAllowed = round($poItem->quantity * ($tolerancePercent / 100.0), 2);
            $maxAllowedTotal = $remaining + $maxOverDeliveryAllowed;

            $isOverDelivery = false;
            $overDeliveryQty = 0.0;

            if ($qtyReceived > $remaining) {
                if ($qtyReceived <= $maxAllowedTotal) {
                    $isOverDelivery = true;
                    $overDeliveryQty = round($qtyReceived - $remaining, 2);
                } else {
                    $unitLabel = ($conversionReceived['is_converted']) 
                        ? "{$rawQtyReceived} {$receivedUnit} (= {$qtyReceived} {$poItem->unit})" 
                        : "{$qtyReceived} {$poItem->unit}";

                    return back()->withErrors([
                        'items' => "Jumlah fisik diterima untuk '{$poItem->item_name}' ({$unitLabel}) melebihi sisa pesanan ({$remaining} {$poItem->unit}) serta melampaui batas toleransi Over-Delivery {$tolerancePercent}% (Maksimum toleransi: {$maxAllowedTotal} {$poItem->unit}). Kelebihan fisik wajib diretur ke vendor atau terbitkan Addendum PO."
                    ])->withInput();
                }
            }

            if ($rawQtyRejected > $rawQtyReceived) {
                return back()->withErrors([
                    'items' => "Jumlah barang cacat/rusak untuk '{$poItem->item_name}' ({$rawQtyRejected} {$receivedUnit}) tidak boleh melebihi jumlah fisik yang tiba ({$rawQtyReceived} {$receivedUnit})."
                ])->withInput();
            }

            if ($qtyRejected > 0 && empty(trim($itemData['notes'] ?? ''))) {
                return back()->withErrors([
                    'items' => "Terdapat {$qtyRejected} unit barang cacat/rusak pada '{$poItem->item_name}'. Mohon berikan keterangan alasan kerusakan pada kolom catatan."
                ])->withInput();
            }

            $totalReceivedInThisBatch += $qtyReceived;
            $itemsPayload[] = [
                'po_item' => $poItem,
                'received_unit' => $receivedUnit,
                'raw_quantity_received' => $rawQtyReceived,
                'raw_quantity_rejected' => $rawQtyRejected,
                'conversion_multiplier' => $conversionMultiplier,
                'quantity_received' => $qtyReceived,
                'quantity_rejected' => $qtyRejected,
                'quantity_accepted' => max(0, $qtyReceived - $qtyRejected),
                'is_over_delivery' => $isOverDelivery,
                'over_delivery_quantity' => $overDeliveryQty,
                'notes' => $itemData['notes'] ?? null,
            ];
        }

        if ($request->receipt_type === 'service') {
            $serviceProgressInput = (float) ($request->progress_percentage ?? 0);
            if ($serviceProgressInput <= 0 && $totalReceivedInThisBatch <= 0) {
                return back()->withErrors([
                    'progress_percentage' => 'Persentase progres pekerjaan untuk BAST termin jasa harus lebih dari 0%.'
                ])->withInput();
            }
        } else {
            if ($totalReceivedInThisBatch <= 0) {
                return back()->withErrors([
                    'items' => 'Minimal harus ada 1 barang dengan jumlah kuantitas fisik tiba lebih dari 0 unit.'
                ])->withInput();
            }
        }

        $user = auth()->user();

        $goodsReceipt = DB::transaction(function () use ($request, $purchaseOrder, $itemsPayload, $user) {
            $receiptType = $request->receipt_type;
            $grNumber = GoodsReceipt::generateGrNumber($receiptType);

            // Handle file uploads (saved in secure local disk)
            $deliveryDocPath = null;
            if ($request->hasFile('delivery_note_doc')) {
                $deliveryDocPath = $request->file('delivery_note_doc')->store('private/receipt_docs', 'local');
            }

            $bastDocPath = null;
            if ($request->hasFile('bast_document')) {
                $bastDocPath = $request->file('bast_document')->store('private/bast_docs', 'local');
            }

            $terminName = null;
            $progressPct = 0.0;
            $cumulativeProgress = 0.0;
            $nominalClaimed = 0.0;

            if ($receiptType === 'service') {
                $previousProgress = (float) $purchaseOrder->goodsReceipts()
                    ->where('receipt_type', 'service')
                    ->sum('progress_percentage');
                $progressPct = (float) ($request->progress_percentage ?? 100);
                $cumulativeProgress = min(100, round($previousProgress + $progressPct, 2));
                $nominalClaimed = $request->filled('nominal_claimed')
                    ? (float) $request->nominal_claimed
                    : round(($progressPct / 100) * $purchaseOrder->grand_total, 2);
                $nextTerminCount = $purchaseOrder->goodsReceipts()->where('receipt_type', 'service')->count() + 1;
                $terminName = $request->termin_name ?: "Termin {$nextTerminCount} ({$progressPct}%)";
            }

            $gr = GoodsReceipt::create([
                'gr_number' => $grNumber,
                'purchase_order_id' => $purchaseOrder->id,
                'received_by' => $user->id,
                'receipt_type' => $receiptType,
                'termin_name' => $terminName,
                'progress_percentage' => $progressPct,
                'cumulative_progress_percentage' => $cumulativeProgress,
                'nominal_claimed' => $nominalClaimed,
                'received_date' => $request->received_date,
                'delivery_note_no' => $request->delivery_note_no,
                'delivery_note_doc' => $deliveryDocPath,
                'item_condition' => $request->item_condition,
                'inspection_notes' => $request->inspection_notes,
                'status' => 'partially_received', // will be updated below
                'service_period_start' => $request->service_period_start,
                'service_period_end' => $request->service_period_end,
                'service_deliverables' => $request->service_deliverables,
                'acceptance_approver_name' => $request->acceptance_approver_name,
                'bast_document_path' => $bastDocPath,
            ]);

            $totalRejectedInGr = 0;

            // Save items and update po_items received_quantity strictly with usable accepted items
            foreach ($itemsPayload as $itemInfo) {
                $poItem = $itemInfo['po_item'];
                $qtyReceived = $itemInfo['quantity_received'];
                $qtyRejected = $itemInfo['quantity_rejected'];
                $qtyAccepted = $itemInfo['quantity_accepted'];
                $totalRejectedInGr += $qtyRejected;

                $gr->items()->create([
                    'po_item_id' => $poItem->id,
                    'received_unit' => $itemInfo['received_unit'],
                    'raw_quantity_received' => $itemInfo['raw_quantity_received'],
                    'raw_quantity_rejected' => $itemInfo['raw_quantity_rejected'],
                    'conversion_multiplier' => $itemInfo['conversion_multiplier'],
                    'quantity_received' => $qtyReceived,
                    'quantity_rejected' => $qtyRejected,
                    'is_over_delivery' => $itemInfo['is_over_delivery'],
                    'over_delivery_quantity' => $itemInfo['over_delivery_quantity'],
                    'notes' => $itemInfo['notes'],
                ]);

                if ($receiptType === 'service') {
                    if ($cumulativeProgress >= 100) {
                        $poItem->update(['received_quantity' => $poItem->quantity]);
                    }
                } else {
                    if ($itemInfo['is_over_delivery']) {
                        $poItem->update([
                            'received_quantity' => $poItem->quantity,
                            'over_delivered_quantity' => $poItem->over_delivered_quantity + $itemInfo['over_delivery_quantity'],
                        ]);
                    } else {
                        $poItem->increment('received_quantity', $qtyAccepted);
                    }
                }
            }

            // Recalculate PO status automatically (issued -> partially_received -> completed)
            $purchaseOrder->recalculateStatus();
            $purchaseOrder->refresh();

            // Set GR status: if there are rejected items, mark as 'disputed'
            if ($totalRejectedInGr > 0) {
                $grStatus = 'disputed';
            } elseif ($purchaseOrder->status === 'completed') {
                $grStatus = 'completed';
            } else {
                $grStatus = 'partially_received';
            }

            $gr->update(['status' => $grStatus]);

            // Record status history audit
            $label = ($receiptType === 'service') ? 'Berita Acara Serah Terima (BAST)' : 'Tanda Terima Barang (Goods Receipt)';
            $historyNote = ($receiptType === 'service')
                ? "BAST #{$grNumber} ({$terminName} - Progres {$progressPct}%, Kumulatif {$cumulativeProgress}%) dicatat oleh {$user->name}. Status PO: {$purchaseOrder->status_label}."
                : "{$label} #{$grNumber} dicatat untuk PO #{$purchaseOrder->po_number}. Status PO saat ini: {$purchaseOrder->status_label}.";

            StatusHistory::create([
                'purchase_request_id' => $purchaseOrder->purchase_request_id,
                'from_status' => $purchaseOrder->purchaseRequest?->status,
                'to_status' => $purchaseOrder->purchaseRequest?->fresh()->status ?? 'processing',
                'user_id' => $user->id,
                'notes' => $historyNote,
            ]);

            // Notify Requester
            $notifMsg = ($receiptType === 'service')
                ? "Berita Acara Serah Terima (BAST) untuk pengadaan jasa '{$purchaseOrder->purchaseRequest?->title}' telah dicatat ({$terminName} - Progres Kumulatif {$cumulativeProgress}%)."
                : "Barang pesanan Anda untuk '{$purchaseOrder->purchaseRequest?->title}' telah diterima di kantor oleh tim logistik.";

            InAppNotification::create([
                'user_id' => $purchaseOrder->purchaseRequest?->user_id,
                'title' => ($receiptType === 'service') ? "Serah Terima Jasa BAST (#{$grNumber})" : "Penerimaan Barang/Jasa (#{$grNumber})",
                'message' => $notifMsg,
                'link' => route('purchase-orders.show', $purchaseOrder),
                'type' => 'goods_received',
            ]);

            return $gr;
        });

        $successMsg = ($request->receipt_type === 'service')
            ? "Berita Acara Serah Terima (BAST) #{$goodsReceipt->gr_number} ({$goodsReceipt->termin_name} - Progres {$goodsReceipt->progress_percentage}%) berhasil dicatat."
            : "Tanda Terima Barang (Goods Receipt) #{$goodsReceipt->gr_number} berhasil dicatat.";

        // Audit Trail
        AuditTrailService::record(
            action: 'gr_created',
            entity: $goodsReceipt,
            entityLabel: $goodsReceipt->gr_number,
            beforeState: null,
            afterState: ['po_number' => $purchaseOrder->po_number, 'status' => $goodsReceipt->status],
            description: "{$user->name} mencatat penerimaan {$goodsReceipt->gr_number} untuk PO {$purchaseOrder->po_number}.",
            request: $request,
        );

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

    /**
     * Poin 5: Secure Stream Document for Delivery Note
     */
    public function downloadDeliveryNote(GoodsReceipt $goodsReceipt)
    {
        if (!$goodsReceipt->delivery_note_doc) {
            abort(404, 'Berkas surat jalan tidak ditemukan.');
        }

        $disk = Storage::disk('local')->exists($goodsReceipt->delivery_note_doc) ? 'local' : 'public';
        if (!Storage::disk($disk)->exists($goodsReceipt->delivery_note_doc)) {
            abort(404, 'File surat jalan tidak ditemukan di server.');
        }

        return Storage::disk($disk)->response($goodsReceipt->delivery_note_doc);
    }

    /**
     * Poin 5: Secure Stream Document for BAST
     */
    public function downloadBast(GoodsReceipt $goodsReceipt)
    {
        if (!$goodsReceipt->bast_document_path) {
            abort(404, 'Berkas BAST resmi tidak ditemukan.');
        }

        $disk = Storage::disk('local')->exists($goodsReceipt->bast_document_path) ? 'local' : 'public';
        if (!Storage::disk($disk)->exists($goodsReceipt->bast_document_path)) {
            abort(404, 'File BAST tidak ditemukan di server.');
        }

        return Storage::disk($disk)->response($goodsReceipt->bast_document_path);
    }
}
