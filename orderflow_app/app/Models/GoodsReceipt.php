<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoodsReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'gr_number',
        'purchase_order_id',
        'received_by',
        'receipt_type',
        'received_date',
        'delivery_note_no',
        'delivery_note_doc',
        'item_condition',
        'inspection_notes',
        'status',
        'service_period_start',
        'service_period_end',
        'service_deliverables',
        'acceptance_approver_name',
        'bast_document_path',
    ];

    protected $casts = [
        'received_date' => 'date',
        'service_period_start' => 'date',
        'service_period_end' => 'date',
    ];

    public const STATUS_LABELS = [
        'partially_received' => 'Penerimaan Sebagian',
        'completed' => 'Lengkap (100% Selesai)',
        'disputed' => 'Bermasalah / Ada Kerusakan',
    ];

    public const STATUS_BADGE_CLASSES = [
        'partially_received' => 'bg-amber-50 text-amber-700 border-amber-200',
        'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'disputed' => 'bg-rose-50 text-rose-700 border-rose-200',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(GrItem::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return self::STATUS_BADGE_CLASSES[$this->status] ?? 'bg-slate-100 text-slate-700 border-slate-200';
    }

    public function getIsServiceAttribute(): bool
    {
        return $this->receipt_type === 'service';
    }

    /**
     * Generate sequential GR number (GR-YYYYMM-XXXX or BAST-YYYYMM-XXXX)
     */
    public static function generateGrNumber(string $type = 'goods'): string
    {
        $tag = ($type === 'service') ? 'BAST' : 'GR';
        $prefix = $tag . '-' . date('Ym') . '-';
        $latest = self::where('gr_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $lastNumber = (int) substr($latest->gr_number, -4);
            $nextNumber = str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return $prefix . $nextNumber;
    }
}
