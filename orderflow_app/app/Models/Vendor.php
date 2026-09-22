<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category',
        'contact_person',
        'email',
        'phone',
        'address',
        'tax_number',
        'bank_name',
        'bank_account_no',
        'bank_account_name',
        'rating',
        'is_active',
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }
}

