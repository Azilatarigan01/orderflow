<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrApprovalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tier_level' => (int) $this->tier_level,
            'role_required' => $this->role_required,
            'role_label' => match($this->role_required) {
                'manager' => 'Manager Divisi',
                'finance' => 'Finance Controller',
                'hod' => 'Head of Department / Direksi',
                default => strtoupper($this->role_required),
            },
            'status' => $this->status,
            'notes' => $this->notes,
            'approver' => $this->approver ? [
                'id' => $this->approver->id,
                'name' => $this->approver->name,
                'email' => $this->approver->email,
                'role' => $this->approver->role,
            ] : null,
            'approved_at' => $this->approved_at?->toIso8601String(),
        ];
    }
}
