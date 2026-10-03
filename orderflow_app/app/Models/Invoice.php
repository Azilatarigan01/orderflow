<?php

namespace App\Models;

use App\Services\DocumentNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'internal_invoice_number',
        'purchase_order_id',
        'vendor_id',
        'invoice_date',
        'due_date',
        'subtotal',
        'tax_amount',
        'penalty_deduction',
        'net_payable_amount',
        'matching_status',
        'matching_notes',
        'payment_status',
        'paid_amount',
        'payment_date',
        'payment_reference',
        'payment_method',
        'verified_by',
        'verified_at',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'payment_date' => 'date',
        'verified_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'penalty_deduction' => 'decimal:2',
        'net_payable_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public const MATCHING_STATUS_LABELS = [
        'matched' => 'Lolos 3-Way Match',
        'mismatch_quantity' => 'Selisih Kuantitas (Qty)',
        'mismatch_price' => 'Selisih Harga Satuan',
        'unmatched' => 'Belum Dicocokkan',
    ];

    public const MATCHING_STATUS_BADGES = [
        'matched' => 'bg-emerald-50 text-emerald-700 border-emerald-300',
        'mismatch_quantity' => 'bg-rose-50 text-rose-700 border-rose-300',
        'mismatch_price' => 'bg-rose-50 text-rose-700 border-rose-300',
        'unmatched' => 'bg-slate-100 text-slate-700 border-slate-300',
    ];

    public const PAYMENT_STATUS_LABELS = [
        'unpaid' => 'Belum Dibayar',
        'partially_paid' => 'Dibayar Sebagian',
        'paid' => 'Lunas (Paid)',
        'disputed' => 'Dispute / Ditahan',
    ];

    public const PAYMENT_STATUS_BADGES = [
        'unpaid' => 'bg-amber-50 text-amber-700 border-amber-300',
        'partially_paid' => 'bg-blue-50 text-blue-700 border-blue-300',
        'paid' => 'bg-emerald-50 text-emerald-700 border-emerald-300',
        'disputed' => 'bg-rose-50 text-rose-700 border-rose-300',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function getMatchingStatusLabelAttribute(): string
    {
        return self::MATCHING_STATUS_LABELS[$this->matching_status] ?? ucfirst($this->matching_status);
    }

    public function getMatchingStatusBadgeClassAttribute(): string
    {
        return self::MATCHING_STATUS_BADGES[$this->matching_status] ?? 'bg-slate-100 text-slate-700 border-slate-200';
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return self::PAYMENT_STATUS_LABELS[$this->payment_status] ?? ucfirst($this->payment_status);
    }

    public function getPaymentStatusBadgeClassAttribute(): string
    {
        return self::PAYMENT_STATUS_BADGES[$this->payment_status] ?? 'bg-slate-100 text-slate-700 border-slate-200';
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }

    public function getFormattedTaxAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->tax_amount, 0, ',', '.');
    }

    public function getFormattedPenaltyDeductionAttribute(): string
    {
        return 'Rp ' . number_format($this->penalty_deduction, 0, ',', '.');
    }

    public function getFormattedNetPayableAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->net_payable_amount, 0, ',', '.');
    }

    public function getFormattedPaidAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->paid_amount, 0, ',', '.');
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0.0, (float) $this->net_payable_amount - (float) $this->paid_amount);
    }

    public function getFormattedRemainingAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->remaining_amount, 0, ',', '.');
    }

    public static function generateInternalInvoiceNumber(string $scope = 'FIN'): string
    {
        return DocumentNumberService::generateInvoiceNumber($scope);
    }
}
