<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatusHistory extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'purchase_request_id',
        'from_status',
        'to_status',
        'user_id',
        'notes',
        'created_at',
    ];

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getToStatusLabelAttribute(): string
    {
        return match ($this->to_status) {
            'draft' => 'Draf Dibuat',
            'submitted' => 'Diajukan (Submitted)',
            'revision_required' => 'Permintaan Revisi',
            'approved' => 'Disetujui (Approved)',
            'rejected' => 'Ditolak (Rejected)',
            'processing' => 'Proses Pengadaan',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->to_status),
        };
    }
}
