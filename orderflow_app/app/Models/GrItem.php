<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'goods_receipt_id',
        'po_item_id',
        'received_unit',
        'raw_quantity_received',
        'raw_quantity_rejected',
        'conversion_multiplier',
        'quantity_received',
        'quantity_rejected',
        'is_over_delivery',
        'over_delivery_quantity',
        'notes',
    ];

    protected $casts = [
        'quantity_received' => 'integer',
        'quantity_rejected' => 'integer',
        'is_over_delivery' => 'boolean',
        'over_delivery_quantity' => 'float',
        'raw_quantity_received' => 'float',
        'raw_quantity_rejected' => 'float',
        'conversion_multiplier' => 'float',
    ];

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function poItem(): BelongsTo
    {
        return $this->belongsTo(PoItem::class);
    }

    /**
     * Quantity of good condition items accepted by the warehouse / company
     */
    public function getQuantityAcceptedAttribute(): int
    {
        return max(0, (int) $this->quantity_received - (int) $this->quantity_rejected);
    }

    /**
     * Check if item has UoM conversion applied
     */
    public function hasUomConversion(): bool
    {
        if (empty($this->received_unit)) {
            return false;
        }

        return strtoupper(trim($this->received_unit)) !== strtoupper(trim((string) $this->poItem?->unit));
    }

    /**
     * Formatted string of original physical received vs converted PO fulfillment
     */
    public function getFormattedPhysicalReceivedAttribute(): string
    {
        if ($this->hasUomConversion()) {
            return "{$this->raw_quantity_received} {$this->received_unit} (= {$this->quantity_received} {$this->poItem?->unit})";
        }

        return "{$this->quantity_received} {$this->poItem?->unit}";
    }
}
