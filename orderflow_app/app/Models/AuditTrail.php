<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditTrail extends Model
{
    // Audit trail is immutable — no updated_at
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'entity_label',
        'before_state',
        'after_state',
        'description',
        'comment',
        'ip_address',
        'user_agent',
        'previous_hash',
        'record_hash',
        'created_at',
    ];

    protected $casts = [
        'before_state' => 'array',
        'after_state'  => 'array',
        'created_at'   => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Action label mapping for display
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'pr_created'       => 'PR Dibuat',
            'pr_submitted'     => 'PR Diajukan',
            'pr_approved'      => 'PR Disetujui',
            'pr_rejected'      => 'PR Ditolak',
            'pr_revision'      => 'Revisi Diminta',
            'pr_cancelled'     => 'PR Dibatalkan',
            'po_created'       => 'PO Dibuat',
            'po_cancelled'     => 'PO Dibatalkan',
            'gr_created'       => 'Penerimaan Barang Dicatat',
            'quotation_created' => 'Quotation Ditambahkan',
            'vendor_selected'  => 'Vendor Dipilih',
            default            => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }

    /**
     * Badge color per action
     */
    public function getActionBadgeClassAttribute(): string
    {
        return match (true) {
            str_contains($this->action, 'approved') || str_contains($this->action, 'created') =>
                'bg-emerald-50 text-emerald-700 border-emerald-200',
            str_contains($this->action, 'rejected') || str_contains($this->action, 'cancelled') =>
                'bg-rose-50 text-rose-700 border-rose-200',
            str_contains($this->action, 'revision') =>
                'bg-amber-50 text-amber-700 border-amber-200',
            str_contains($this->action, 'submitted') =>
                'bg-sky-50 text-sky-700 border-sky-200',
            default =>
                'bg-slate-100 text-slate-700 border-slate-200',
        };
    }
}
