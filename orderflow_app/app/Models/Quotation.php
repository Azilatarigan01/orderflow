<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_request_id',
        'vendor_id',
        'quotation_number',
        'subtotal',
        'shipping_cost',
        'tax_amount',
        'grand_total',
        'estimated_delivery_days',
        'warranty_months',
        'warranty_info',
        'valid_until',
        'file_path',
        'notes',
        'score',
        'is_selected',
        'selection_reason',
        'is_single_source',
        'single_source_reason',
        'created_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'score' => 'decimal:2',
        'estimated_delivery_days' => 'integer',
        'warranty_months' => 'integer',
        'valid_until' => 'date',
        'is_selected' => 'boolean',
        'is_single_source' => 'boolean',
    ];

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if quotation is expired
     */
    public function getIsExpiredAttribute(): bool
    {
        if (!$this->valid_until) {
            return false;
        }

        return $this->valid_until->isBefore(Carbon::today());
    }

    /**
     * Formatted currency accessors
     */
    public function getFormattedGrandTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->grand_total, 0, ',', '.');
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }

    public function getFormattedShippingCostAttribute(): string
    {
        return 'Rp ' . number_format($this->shipping_cost, 0, ',', '.');
    }

    public function getFormattedTaxAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->tax_amount, 0, ',', '.');
    }
}
