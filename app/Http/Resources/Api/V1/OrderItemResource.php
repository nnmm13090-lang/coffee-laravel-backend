<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'product_id' => $this->product_id,
            'name' => $this->name,
            'price' => (float) $this->price,
            'quantity' => $this->quantity,
            'line_total' => round($this->price * $this->quantity, 2),
        ];
    }
}
