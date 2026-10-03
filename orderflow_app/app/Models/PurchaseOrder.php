<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'purchase_request_id',
        'vendor_id',
        'quotation_id',
        'issued_by',
        'order_date',
        'delivery_target_date',
        'subtotal',
        'tax_rate',
        'tax_calculation_mode',
        'tax_rounding_tolerance',
        'shipping_fee',
        'tax_amount',
        'tax_rounding_difference',
        'grand_total',
        'over_delivery_tolerance_percentage',
        'payment_terms',
        'status',
        'is_short_closed',
        'short_closed_at',
        'short_closed_by',
        'short_close_reason',
        'notes',
        'cancellation_reason',
        'cancelled_at',
        'default_notice_sent_at',
        'default_notice_count',
    ];

    protected $casts = [
        'order_date' => 'date',
        'delivery_target_date' => 'date',
        'cancelled_at' => 'datetime',
        'is_short_closed' => 'boolean',
        'short_closed_at' => 'datetime',
        'default_notice_sent_at' => 'datetime',
        'default_notice_count' => 'integer',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_rounding_tolerance' => 'decimal:2',
        'tax_rounding_difference' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'over_delivery_tolerance_percentage' => 'decimal:2',
    ];

    public function shortClosedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'short_closed_by');
    }

    public function getFormattedTaxRateAttribute(): string
    {
        return number_format($this->tax_rate ?? 11, 0) . '%';
    }

    public function getFormattedRoundingDifferenceAttribute(): string
    {
        return 'Rp ' . number_format($this->tax_rounding_difference ?? 0, 0, ',', '.');
    }

    public function getTaxCalculationModeLabelAttribute(): string
    {
        return ($this->tax_calculation_mode === 'header_subtotal')
            ? 'Header Subtotal (Akumulasi Total)'
            : 'Line Item (Rincian per Baris)';
    }

    public const STATUS_LABELS = [
        'draft' => 'Draf PO',
        'issued' => 'Diterbitkan (Issued)',
        'partially_received' => 'Diterima Sebagian',
        'completed' => 'Selesai (Completed)',
        'cancelled' => 'Dibatalkan',
    ];

    public const STATUS_BADGE_CLASSES = [
        'draft' => 'bg-slate-100 text-slate-700 border-slate-300',
        'issued' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        'partially_received' => 'bg-amber-50 text-amber-700 border-amber-200',
        'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'cancelled' => 'bg-rose-50 text-rose-700 border-rose-200',
    ];

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PoItem::class);
    }

    public function goodsReceipts(): HasMany
    {
        return $this->hasMany(GoodsReceipt::class)->orderBy('received_date', 'desc');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class)->latest();
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return self::STATUS_BADGE_CLASSES[$this->status] ?? 'bg-slate-100 text-slate-700 border-slate-200';
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }

    public function getFormattedShippingFeeAttribute(): string
    {
        return 'Rp ' . number_format($this->shipping_fee, 0, ',', '.');
    }

    public function getFormattedTaxAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->tax_amount, 0, ',', '.');
    }

    public function getFormattedGrandTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->grand_total, 0, ',', '.');
    }

    public function getTotalOrderedQuantityAttribute(): int
    {
        return (int) $this->items->sum('quantity');
    }

    public function getTotalReceivedQuantityAttribute(): int
    {
        return (int) $this->items->sum('received_quantity');
    }

    public function getIsServiceAttribute(): bool
    {
        return $this->goodsReceipts()->where('receipt_type', 'service')->exists()
            || $this->items()->whereIn('unit', ['Paket', 'Layanan', 'Bulan', 'Proyek', 'Sesi'])->exists();
    }

    public function getServiceCumulativeProgressPercentageAttribute(): float
    {
        return (float) $this->goodsReceipts()
            ->where('receipt_type', 'service')
            ->sum('progress_percentage');
    }

    public function getReceiptProgressPercentageAttribute(): int
    {
        $serviceProgress = (float) $this->goodsReceipts()
            ->where('receipt_type', 'service')
            ->sum('progress_percentage');

        if ($serviceProgress > 0) {
            return min(100, (int) round($serviceProgress));
        }

        $ordered = $this->total_ordered_quantity;
        if ($ordered <= 0) {
            return 0;
        }

        return min(100, (int) round(($this->total_received_quantity / $ordered) * 100));
    }

    /**
     * Check if PO is overdue past delivery target date
     */
    public function getIsOverdueAttribute(): bool
    {
        if (!in_array($this->status, ['issued', 'partially_received'])) {
            return false;
        }

        if (!$this->delivery_target_date) {
            return false;
        }

        return today()->gt($this->delivery_target_date);
    }

    /**
     * Number of overdue calendar days
     */
    public function getOverdueDaysAttribute(): int
    {
        if (!$this->is_overdue || !$this->delivery_target_date) {
            return 0;
        }

        return (int) $this->delivery_target_date->diffInDays(today());
    }

    /**
     * Enterprise Liquidated Damages Penalty Percentage
     * Standard: 1‰ (0.1%) per calendar day of delay, capped at 5.0% maximum.
     */
    public function getPenaltyPercentageAttribute(): float
    {
        if (!$this->is_overdue) {
            return 0.0;
        }

        $calc = round($this->overdue_days * 0.1, 2);
        return (float) min(5.0, $calc);
    }

    /**
     * Estimated penalty amount in Rupiah based on PO Grand Total
     */
    public function getEstimatedPenaltyAmountAttribute(): float
    {
        if (!$this->is_overdue) {
            return 0.0;
        }

        return round(((float) $this->grand_total * $this->penalty_percentage) / 100, 2);
    }

    /**
     * Formatted penalty amount in Rupiah
     */
    public function getFormattedEstimatedPenaltyAttribute(): string
    {
        return 'Rp ' . number_format($this->estimated_penalty_amount, 0, ',', '.');
    }

    /**
     * Scope query to only overdue purchase orders
     */
    public function scopeOverdue($query)
    {
        return $query->whereIn('status', ['issued', 'partially_received'])
            ->whereNotNull('delivery_target_date')
            ->where('delivery_target_date', '<', today()->toDateString());
    }

    /**
     * Recalculate status based on quantities received or service progress percentage
     */
    public function recalculateStatus(): void
    {
        if ($this->status === 'cancelled' || $this->is_short_closed) {
            return;
        }

        $this->load(['items', 'goodsReceipts']);
        
        $serviceProgress = (float) $this->goodsReceipts
            ->where('receipt_type', 'service')
            ->sum('progress_percentage');

        if ($serviceProgress > 0 || $this->is_service) {
            if ($serviceProgress <= 0) {
                if ($this->status !== 'draft') {
                    $this->update(['status' => 'issued']);
                }
            } elseif ($serviceProgress < 100) {
                $this->update(['status' => 'partially_received']);
            } else {
                $this->update(['status' => 'completed']);
                foreach ($this->items as $item) {
                    $item->update(['received_quantity' => $item->quantity]);
                }
            }
        } else {
            $ordered = $this->items->sum('quantity');
            $received = $this->items->sum('received_quantity');

            if ($received <= 0) {
                if ($this->status !== 'draft') {
                    $this->update(['status' => 'issued']);
                }
            } elseif ($received < $ordered) {
                $this->update(['status' => 'partially_received']);
            } else {
                $this->update(['status' => 'completed']);
            }
        }

        // Automatically recalculate and synchronize parent PR status
        if ($this->purchaseRequest) {
            $this->purchaseRequest->recalculateStatus();
        }
    }

    /**
     * Generate Enterprise sequential PO number with atomic lock
     * Format: PO/{SCOPE}/{YEAR}/{MONTH}/{XXXX} (e.g. PO/PROC/2026/09/0001)
     */
    public static function generatePoNumber(string $scope = 'PROC'): string
    {
        return \App\Services\DocumentNumberService::generatePoNumber($scope);
    }
}
