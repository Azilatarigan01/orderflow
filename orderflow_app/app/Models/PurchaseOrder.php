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
        'shipping_fee',
        'tax_amount',
        'grand_total',
        'payment_terms',
        'status',
        'notes',
    ];

    protected $casts = [
        'order_date' => 'date',
        'delivery_target_date' => 'date',
        'subtotal' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

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

    public function getReceiptProgressPercentageAttribute(): int
    {
        $ordered = $this->total_ordered_quantity;
        if ($ordered <= 0) {
            return 0;
        }

        return min(100, (int) round(($this->total_received_quantity / $ordered) * 100));
    }

    /**
     * Recalculate status based on quantities received
     */
    public function recalculateStatus(): void
    {
        $this->load('items');
        $ordered = $this->items->sum('quantity');
        $received = $this->items->sum('received_quantity');

        if ($received <= 0) {
            // Keep issued or draft
            if ($this->status !== 'draft') {
                $this->update(['status' => 'issued']);
            }
        } elseif ($received < $ordered) {
            $this->update(['status' => 'partially_received']);
        } else {
            $this->update(['status' => 'completed']);

            // When PO is completed, also mark the PR as completed
            if ($this->purchaseRequest && $this->purchaseRequest->status !== 'completed') {
                $this->purchaseRequest->update(['status' => 'completed']);
            }
        }
    }

    /**
     * Generate sequential PO number (PO-YYYYMM-XXXX)
     */
    public static function generatePoNumber(): string
    {
        $prefix = 'PO-' . date('Ym') . '-';
        $latest = self::where('po_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $lastNumber = (int) substr($latest->po_number, -4);
            $nextNumber = str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return $prefix . $nextNumber;
    }
}
