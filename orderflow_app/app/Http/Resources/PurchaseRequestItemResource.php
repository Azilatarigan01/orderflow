<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseRequestItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'item_name' => $this->item_name,
            'specification' => $this->specification,
            'quantity' => (int) $this->quantity,
            'unit' => $this->unit,
            'estimated_unit_price' => (float) $this->estimated_unit_price,
            'estimated_subtotal' => (float) $this->estimated_subtotal,
            'formatted_unit_price' => 'Rp ' . number_format($this->estimated_unit_price, 0, ',', '.'),
            'formatted_subtotal' => 'Rp ' . number_format($this->estimated_subtotal, 0, ',', '.'),
        ];
    }
}
