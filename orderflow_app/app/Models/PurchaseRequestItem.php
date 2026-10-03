<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_request_id',
        'item_name',
        'specification',
        'quantity',
        'unit',
        'estimated_unit_price',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'estimated_unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::saving(function ($item) {
            $item->subtotal = $item->quantity * $item->estimated_unit_price;
        });

        static::updating(function ($item) {
            if ($item->purchaseRequest && in_array($item->purchaseRequest->status, ['approved', 'po_created', 'completed'])) {
                throw new \DomainException("Item PR yang sudah disetujui atau sedang dalam proses PO tidak dapat diubah spesifikasi dan harganya demi menjaga keaslian data snapshot audit.");
            }
        });

        static::deleting(function ($item) {
            if ($item->purchaseRequest && in_array($item->purchaseRequest->status, ['approved', 'po_created', 'completed'])) {
                throw new \DomainException("Item PR yang sudah disetujui tidak dapat dihapus demi menjaga integritas data audit.");
            }
        });
    }

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }
}
