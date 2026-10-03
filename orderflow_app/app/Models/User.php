<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'department_id',
        'phone',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function hasRole(string|array $roles): bool
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles);
        }
        return $this->role === $roles;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isProcurement(): bool
    {
        return $this->role === 'procurement';
    }

    public function isFinance(): bool
    {
        return $this->role === 'finance';
    }

    public function isRequester(): bool
    {
        return $this->role === 'requester';
    }

    public function isAuditor(): bool
    {
        return $this->role === 'auditor';
    }

    public function isHod(): bool
    {
        return $this->role === 'hod';
    }

    public function isWarehouse(): bool
    {
        return $this->role === 'warehouse';
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'System Administrator',
            'manager' => 'Department Manager',
            'procurement' => 'Procurement Officer',
            'finance' => 'Finance & Accounting',
            'auditor' => 'Internal Auditor',
            'hod' => 'Head of Department / Direksi',
            'warehouse' => 'Staf Gudang & Logistik',
            default => 'Employee / Requester',
        };
    }

    public function actingDelegationsGiven()
    {
        return $this->hasMany(ActingDelegation::class, 'delegator_user_id');
    }

    public function actingDelegationsReceived()
    {
        return $this->hasMany(ActingDelegation::class, 'delegatee_user_id');
    }

    /**
     * Get currently active acting delegation granted to this user for a given role
     */
    public function getActiveActingDelegationFor(string $role, ?int $departmentId = null): ?ActingDelegation
    {
        $query = $this->actingDelegationsReceived()
            ->activeNow()
            ->where('role_delegated', $role);

        if ($departmentId !== null) {
            $query->whereHas('delegator', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        return $query->with('delegator')->first();
    }
}
