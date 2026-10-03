<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pr_number' => $this->pr_number,
            'title' => $this->title,
            'description' => $this->description,
            'required_date' => $this->required_date ? $this->required_date->format('Y-m-d') : null,
            'estimated_total' => (float) $this->estimated_total,
            'formatted_estimated_total' => 'Rp ' . number_format($this->estimated_total, 0, ',', '.'),
            'status' => $this->status,
            'status_label' => $this->status_label ?? ucfirst($this->status),
            'user' => $this->relationLoaded('user') && $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'role' => $this->user->role,
            ] : null,
            'department' => $this->relationLoaded('department') && $this->department ? [
                'id' => $this->department->id,
                'name' => $this->department->name,
                'code' => $this->department->code,
            ] : null,
            'items' => PurchaseRequestItemResource::collection($this->whenLoaded('items')),
            'items_count' => $this->items_count ?? ($this->relationLoaded('items') ? $this->items->count() : $this->items()->count()),
            'approvals' => PrApprovalResource::collection($this->whenLoaded('approvals')),
            'current_approval_tier' => $this->currentPendingApproval() ? new PrApprovalResource($this->currentPendingApproval()) : null,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
