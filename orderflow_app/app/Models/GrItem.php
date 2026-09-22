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
        'quantity_received',
        'quantity_rejected',
        'notes',
    ];

    protected $casts = [
        'quantity_received' => 'integer',
        'quantity_rejected' => 'integer',
    ];

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function poItem(): BelongsTo
    {
        return $this->belongsTo(PoItem::class);
    }
}
