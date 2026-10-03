<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PoItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'purchase_request_item_id',
        'item_name',
        'specification',
        'quantity',
        'unit',
        'unit_price',
        'subtotal',
        'received_quantity',
        'over_delivery_tolerance_percentage',
        'over_delivered_quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'received_quantity' => 'integer',
        'over_delivery_tolerance_percentage' => 'decimal:2',
        'over_delivered_quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function purchaseRequestItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequestItem::class);
    }

    public function grItems(): HasMany
    {
        return $this->hasMany(GrItem::class);
    }

    public function getMaxAllowedQuantityAttribute(): float
    {
        $toleranceRate = (float) ($this->over_delivery_tolerance_percentage ?? 5.0) / 100.0;
        return (float) $this->quantity * (1.0 + $toleranceRate);
    }

    public function getMaxOverDeliveryQuantityAttribute(): float
    {
        $toleranceRate = (float) ($this->over_delivery_tolerance_percentage ?? 5.0) / 100.0;
        return round((float) $this->quantity * $toleranceRate, 2);
    }

    public function getRemainingQuantityAttribute(): int
    {
        return max(0, $this->quantity - $this->received_quantity);
    }

    public function getFormattedUnitPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->unit_price, 0, ',', '.');
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }
}
