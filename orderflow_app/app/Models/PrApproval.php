<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_request_id',
        'tier_level',
        'role_required',
        'department_id',
        'approver_id',
        'assigned_approver_id',
        'reassigned_at',
        'reassigned_by',
        'reassign_reason',
        'is_acting',
        'acting_for_user_id',
        'status',
        'notes',
        'acted_at',
    ];

    protected $casts = [
        'tier_level'    => 'integer',
        'is_acting'     => 'boolean',
        'acted_at'      => 'datetime',
        'reassigned_at' => 'datetime',
    ];

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function assignedApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_approver_id');
    }

    public function reassignedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reassigned_by');
    }

    public function actingForUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acting_for_user_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function getTierLabelAttribute(): string
    {
        return match ($this->tier_level) {
            1 => 'Tahap 1: Manager Divisi (' . ($this->department?->code ?? 'Divisi') . ')',
            2 => 'Tahap 2: Verifikasi Anggaran Keuangan (Finance)',
            3 => 'Tahap 3: Otorisasi Direksi / Head of Department',
            default => 'Tahap ' . $this->tier_level,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Keputusan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'revision_required' => 'Minta Revisi',
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
            'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
            'revision_required' => 'bg-orange-50 text-orange-700 border-orange-200',
            default => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }
}
