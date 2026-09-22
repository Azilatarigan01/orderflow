<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

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

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'System Administrator',
            'manager' => 'Department Manager',
            'procurement' => 'Procurement Officer',
            'finance' => 'Finance & Accounting',
            'auditor' => 'Internal Auditor',
            default => 'Employee / Requester',
        };
    }
}
