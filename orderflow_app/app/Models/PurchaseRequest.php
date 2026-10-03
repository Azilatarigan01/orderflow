<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use App\Services\AuditTrailService;

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
        'rfq_deadline',
        'estimated_total',
        'status',
        'attachment_path',
    ];

    protected $casts = [
        'required_date' => 'date',
        'rfq_deadline' => 'datetime',
        'estimated_total' => 'decimal:2',
    ];

    public function getIsRfqClosedAttribute(): bool
    {
        return $this->rfq_deadline ? Carbon::now()->isAfter($this->rfq_deadline) : false;
    }

    public function getFormattedRfqDeadlineAttribute(): ?string
    {
        return $this->rfq_deadline ? $this->rfq_deadline->format('d M Y, H:i') . ' WIB' : null;
    }

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

    public function statusHistories(): HasMany
    {
        return $this->histories();
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
     * Get all awarded quotations for multi-vendor / split PO pengadaan
     */
    public function selectedQuotations()
    {
        return $this->quotations()->where('is_selected', true)->with('vendor')->get();
    }

    /**
     * Get awarded quotations that have not yet had a PO issued
     */
    public function unissuedQuotations()
    {
        $issuedQuotationIds = $this->purchaseOrders()->pluck('quotation_id')->filter()->toArray();
        return $this->quotations()
            ->where('is_selected', true)
            ->whereNotIn('id', $issuedQuotationIds)
            ->with('vendor')
            ->get();
    }

    /**
     * Check if PR has multi-vendor quotations or multiple POs issued
     */
    public function hasMultipleVendors(): bool
    {
        return $this->quotations()->where('is_selected', true)->count() > 1 || $this->purchaseOrders()->count() > 1;
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function purchaseOrder(): ?PurchaseOrder
    {
        return $this->purchaseOrders()->latest()->first();
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
     * Generate Enterprise sequential PR number with department code and atomic lock
     * Format: PR/{DEPT}/{YEAR}/{MONTH}/{XXXX} (e.g. PR/IT/2026/09/0001)
     */
    public static function generatePrNumber($department = null): string
    {
        return \App\Services\DocumentNumberService::generatePrNumber($department);
    }

    /**
     * Scope for Data Isolation and Privacy based on user role
     */
    public function scopeAccessibleBy($query, User $user)
    {
        if ($user->hasRole('admin') || $user->hasRole('procurement') || $user->hasRole('auditor')) {
            return $query;
        }

        if ($user->hasRole('hod')) {
            // HoD / Direksi sees strategic PRs exceeding 25M threshold or their own department's PRs
            return $query->where(function ($q) use ($user) {
                $q->where(function ($sub) {
                    $sub->where('estimated_total', '>', 25000000)
                        ->where('status', '!=', 'draft');
                })->orWhere(function ($sub) use ($user) {
                    $sub->where('department_id', $user->department_id)
                        ->where('status', '!=', 'draft');
                })->orWhere('user_id', $user->id);
            });
        }

        if ($user->hasRole('finance')) {
            // Finance sees non-draft company-wide PRs (> 5M threshold) or any PR in financial processing/invoicing, or own PRs
            return $query->where(function ($q) use ($user) {
                $q->where('status', '!=', 'draft')
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

        // Regular Requester / Warehouse: strictly ONLY their own submitted or draft PRs
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
     * Check if PR can be recalled/withdrawn back to draft by user
     */
    public function canBeWithdrawnBy(User $user): bool
    {
        if ($this->status !== 'submitted') {
            return false;
        }

        return $this->user_id === $user->id || $user->hasRole('admin');
    }

    /**
     * Check if PR can be cancelled by user
     */
    public function canBeCancelledBy(User $user): bool
    {
        if (!in_array($this->status, ['draft', 'submitted'])) {
            return false;
        }

        return $this->user_id === $user->id || $user->hasRole('admin');
    }

    /**
     * Refresh and recalculate total from items
     */
    public function recalculateTotal(): void
    {
        $total = $this->items()->sum('subtotal');
        $this->update(['estimated_total' => $total]);
    }

    /**
     * Recalculate and synchronize PR status based on issued Purchase Orders and fulfillment
     */
    public function recalculateStatus(): void
    {
        // Only evaluate if PR is in active post-approval lifecycle
        if (!in_array($this->status, ['approved', 'processing', 'completed'])) {
            return;
        }

        $this->loadMissing(['items', 'purchaseOrders.items']);

        // Only consider non-cancelled POs
        $activePos = $this->purchaseOrders->where('status', '!=', 'cancelled');

        if ($activePos->isEmpty()) {
            if ($this->status !== 'approved') {
                $this->update(['status' => 'approved']);
            }
            return;
        }

        // Check if all requested items are covered by POs
        $totalPrQty = (float) $this->items->sum('quantity');
        $totalPoOrderedQty = (float) $activePos->flatMap->items->sum('quantity');
        $isQuantityCovered = ($totalPrQty > 0) ? ($totalPoOrderedQty >= $totalPrQty) : false;

        // Check each active PO status: All active POs must be 'completed'
        $allPosCompleted = $activePos->isNotEmpty() && $activePos->every(fn($po) => $po->status === 'completed');

        if ($isQuantityCovered && $allPosCompleted) {
            if ($this->status !== 'completed') {
                $oldStatus = $this->status;
                $this->update(['status' => 'completed']);

                // Status history
                StatusHistory::create([
                    'purchase_request_id' => $this->id,
                    'from_status' => $oldStatus,
                    'to_status' => 'completed',
                    'user_id' => auth()->id() ?? $this->user_id,
                    'notes' => 'Seluruh barang/jasa pengadaan telah diterima lengkap di gudang (seluruh PO selesai). Dokumen PR otomatis tuntas (Completed).',
                ]);

                // Notify Requester
                InAppNotification::create([
                    'user_id' => $this->user_id,
                    'title' => "Pengadaan Selesai (PR #{$this->pr_number})",
                    'message' => "Pengadaan untuk PR '{$this->title}' telah selesai seluruhnya. Seluruh fisik barang/jasa telah diverifikasi & diterima.",
                    'link' => route('purchase-requests.show', $this),
                    'type' => 'pr_completed',
                ]);

                AuditTrailService::record(
                    action: 'pr_completed',
                    entity: $this,
                    entityLabel: $this->pr_number,
                    beforeState: ['status' => $oldStatus],
                    afterState: ['status' => 'completed'],
                    description: "PR {$this->pr_number} otomatis berubah ke completed karena seluruh PO ({$activePos->count()} PO) telah tuntas diterima.",
                );
            }
        } else {
            // Active POs exist, but items are not fully covered or some POs are still issued / partially_received
            if ($this->status !== 'processing') {
                $oldStatus = $this->status;
                $this->update(['status' => 'processing']);

                StatusHistory::create([
                    'purchase_request_id' => $this->id,
                    'from_status' => $oldStatus,
                    'to_status' => 'processing',
                    'user_id' => auth()->id() ?? $this->user_id,
                    'notes' => 'Dokumen PR berstatus Diproses Pengadaan (PO aktif berjalan / pengiriman bertahap).',
                ]);
            }
        }
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
