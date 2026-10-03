<?php

namespace App\Services;

use App\Models\PurchaseOrder;

class ThreeWayMatchingService
{
    /**
     * Perform 3-Way Matching validation between PO, Physical Goods Receipts, and Vendor Invoice
     *
     * @param PurchaseOrder $purchaseOrder
     * @param array $itemsInput Array of ['po_item_id' => int, 'quantity_invoiced' => float, 'unit_price' => float]
     * @return array [
     *   'matching_status' => 'matched' | 'mismatch_quantity' | 'mismatch_price',
     *   'notes' => string,
     *   'subtotal' => float,
     *   'tax_amount' => float,
     *   'penalty_deduction' => float,
     *   'net_payable_amount' => float,
     *   'item_results' => array,
     * ]
     */
    public function performMatching(PurchaseOrder $purchaseOrder, array $itemsInput, ?float $taxAmountOverride = null): array
    {
        $purchaseOrder->load(['items', 'goodsReceipts.items']);

        $mismatches = [];
        $itemResults = [];
        $subtotal = 0.0;
        $hasQtyMismatch = false;
        $hasPriceMismatch = false;

        $poItemsById = $purchaseOrder->items->keyBy('id');

        foreach ($itemsInput as $input) {
            $poItemId = $input['po_item_id'] ?? null;
            $poItem = $poItemsById->get($poItemId);

            if (!$poItem) {
                continue;
            }

            $qtyInvoiced = (float) ($input['quantity_invoiced'] ?? 0);
            $unitPriceInvoiced = (float) ($input['unit_price'] ?? $poItem->unit_price);
            $itemSubtotal = round($qtyInvoiced * $unitPriceInvoiced, 2);
            $subtotal += $itemSubtotal;

            $qtyReceived = (float) $poItem->received_quantity;
            $poUnitPrice = (float) $poItem->unit_price;

            $itemStatus = 'matched';
            $itemRemarks = [];

            // 1. Check Quantity: Invoiced cannot exceed received physical/service deliverables
            if ($qtyInvoiced > $qtyReceived) {
                $hasQtyMismatch = true;
                $diff = $qtyInvoiced - $qtyReceived;
                $itemRemarks[] = "Kuantitas ditagih ({$qtyInvoiced}) melebihi fisik diterima di gudang/BAST ({$qtyReceived}). Selisih: +{$diff} {$poItem->unit}.";
                $itemStatus = 'mismatch_quantity';
            }

            // 2. Check Unit Price: Invoiced cannot exceed agreed PO price
            if ($unitPriceInvoiced > $poUnitPrice) {
                $hasPriceMismatch = true;
                $priceDiff = $unitPriceInvoiced - $poUnitPrice;
                $itemRemarks[] = "Harga satuan faktur (Rp " . number_format($unitPriceInvoiced, 0, ',', '.') . ") lebih tinggi dari PO (Rp " . number_format($poUnitPrice, 0, ',', '.') . "). Selisih: +Rp " . number_format($priceDiff, 0, ',', '.') . ".";
                if ($itemStatus === 'matched') {
                    $itemStatus = 'mismatch_price';
                }
            }

            if (!empty($itemRemarks)) {
                $mismatches[] = "Item '{$poItem->item_name}': " . implode(' | ', $itemRemarks);
            }

            $itemResults[] = [
                'po_item_id' => $poItem->id,
                'item_name' => $poItem->item_name,
                'quantity_ordered' => (float) $poItem->quantity,
                'quantity_received' => $qtyReceived,
                'quantity_invoiced' => $qtyInvoiced,
                'po_unit_price' => $poUnitPrice,
                'invoiced_unit_price' => $unitPriceInvoiced,
                'subtotal' => $itemSubtotal,
                'status' => $itemStatus,
                'remarks' => implode('; ', $itemRemarks),
            ];
        }

        // Determine Overall Matching Status
        if ($hasQtyMismatch) {
            $matchingStatus = 'mismatch_quantity';
            $notes = "DITOLAK/DITAHAN: Ditemukan selisih kuantitas fisik. " . implode(' ', $mismatches);
        } elseif ($hasPriceMismatch) {
            $matchingStatus = 'mismatch_price';
            $notes = "DITOLAK/DITAHAN: Ditemukan selisih harga satuan terhadap kontrak PO. " . implode(' ', $mismatches);
        } else {
            $matchingStatus = 'matched';
            $notes = "3-Way Match Lolos Sempurna: Kuantitas faktur sesuai fisik bukti tanda terima (GR/BAST) dan harga satuan sesuai kesepakatan PO.";
        }

        // Calculate Tax Amount (11% standard or proportional to PO tax rate)
        $taxRate = (float) ($purchaseOrder->tax_rate ?? 11);
        $taxAmount = $taxAmountOverride !== null
            ? $taxAmountOverride
            : round(($subtotal * $taxRate) / 100, 2);

        // Apply Liquidated Damages Penalty Deduction from PO if overdue
        $penaltyDeduction = 0.0;
        if ($purchaseOrder->is_overdue && $purchaseOrder->estimated_penalty_amount > 0) {
            $penaltyDeduction = $purchaseOrder->estimated_penalty_amount;
            $notes .= " Dikenakan klausul denda keterlambatan vendor sebesar Rp " . number_format($penaltyDeduction, 0, ',', '.') . " ({$purchaseOrder->penalty_percentage}%).";
        }

        $netPayableAmount = max(0.0, ($subtotal + $taxAmount) - $penaltyDeduction);

        return [
            'matching_status' => $matchingStatus,
            'matching_notes' => $notes,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'penalty_deduction' => $penaltyDeduction,
            'net_payable_amount' => $netPayableAmount,
            'item_results' => $itemResults,
        ];
    }
}
