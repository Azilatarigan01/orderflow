<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ActingDelegation extends Model
{
    use HasFactory;

    protected $fillable = [
        'delegator_user_id',
        'delegatee_user_id',
        'role_delegated',
        'start_date',
        'end_date',
        'reason',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_active'  => 'boolean',
    ];

    public function delegator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegator_user_id');
    }

    public function delegatee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegatee_user_id');
    }

    /**
     * Scope only delegations active for current date
     */
    public function scopeActiveNow($query)
    {
        $today = Carbon::today()->toDateString();
        return $query->where('is_active', true)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today);
    }

    /**
     * Check if delegation is currently effective
     */
    public function isEffectiveToday(): bool
    {
        $today = Carbon::today();
        return $this->is_active 
            && $this->start_date <= $today 
            && $this->end_date >= $today;
    }
}
