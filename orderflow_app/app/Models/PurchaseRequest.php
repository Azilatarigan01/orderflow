<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class PurchaseRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'pr_number',
        'user_id',
        'department_id',
        'title',
        'description',
        'required_date',
        'estimated_total',
        'status',
        'attachment_path',
    ];

    protected $casts = [
        'required_date' => 'date',
        'estimated_total' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(StatusHistory::class)->orderBy('created_at', 'desc');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(PrApproval::class)->orderBy('tier_level', 'asc');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function selectedQuotation(): ?Quotation
    {
        return $this->quotations()->where('is_selected', true)->first();
    }

    /**
     * Get the active pending approval tier
     */
    public function currentPendingApproval(): ?PrApproval
    {
        if ($this->status !== 'submitted') {
            return null;
        }

        return $this->approvals()
            ->where('status', 'pending')
            ->orderBy('tier_level', 'asc')
            ->first();
    }

    /**
     * Generate standard sequential PR number (PR-YYYYMM-XXXX)
     */
    public static function generatePrNumber(): string
    {
        $prefix = 'PR-' . date('Ym') . '-';
        $latest = self::where('pr_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $lastNumber = (int) substr($latest->pr_number, -4);
            $nextNumber = str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return $prefix . $nextNumber;
    }

    /**
     * Scope for Data Isolation and Privacy based on user role
     */
    public function scopeAccessibleBy($query, User $user)
    {
        if ($user->hasRole('admin') || $user->hasRole('procurement') || $user->hasRole('auditor')) {
            return $query;
        }

        if ($user->hasRole('finance')) {
            // Finance only sees submitted / reviewed PRs, or their own PRs
            return $query->where(function ($q) use ($user) {
                $q->whereIn('status', ['submitted', 'approved', 'processing', 'completed'])
                  ->orWhere('user_id', $user->id);
            });
        }

        if ($user->hasRole('manager')) {
            // Manager sees non-draft PRs from their department to review, or their own PRs
            return $query->where(function ($q) use ($user) {
                $q->where('department_id', $user->department_id)
                  ->where('status', '!=', 'draft')
                  ->orWhere('user_id', $user->id);
            });
        }

        // Regular Requester: strictly ONLY their own submitted or draft PRs
        return $query->where('user_id', $user->id);
    }

    /**
     * Check if PR can be edited by user (only draft or revision_required, and owned by user)
     */
    public function canBeEditedBy(User $user): bool
    {
        if ($user->hasRole('admin')) {
            return in_array($this->status, ['draft', 'revision_required']);
        }

        return $this->user_id === $user->id && in_array($this->status, ['draft', 'revision_required']);
    }

    /**
     * Check if PR can be submitted
     */
    public function canBeSubmittedBy(User $user): bool
    {
        return $this->canBeEditedBy($user) && $this->items()->count() > 0;
    }

    /**
     * Refresh and recalculate total from items
     */
    public function recalculateTotal(): void
    {
        $total = $this->items()->sum('subtotal');
        $this->update(['estimated_total' => $total]);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft (Draf Pengajuan)',
            'submitted' => 'Menunggu Approval (Diajukan)',
            'revision_required' => 'Perlu Revisi',
            'approved' => 'Disetujui (Approved)',
            'rejected' => 'Ditolak (Rejected)',
            'processing' => 'Diproses Pengadaan',
            'completed' => 'Selesai (Completed)',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'bg-slate-100 text-slate-700 border-slate-200',
            'submitted' => 'bg-sky-50 text-sky-700 border-sky-200',
            'revision_required' => 'bg-amber-50 text-amber-700 border-amber-200',
            'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
            'processing' => 'bg-purple-50 text-purple-700 border-purple-200',
            'completed' => 'bg-teal-50 text-teal-700 border-teal-200',
            'cancelled' => 'bg-slate-200 text-slate-600 border-slate-300',
            default => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }
}
