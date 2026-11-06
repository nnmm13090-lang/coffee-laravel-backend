<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'subtotal' => (float) $this->subtotal,
            'discount' => (float) $this->discount,
            'total' => (float) $this->total,
            'coupon_code' => $this->whenLoaded('coupon', fn () => $this->coupon?->code),
            'note' => $this->note,
            'customer' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'phone' => $this->user->phone,
            ]),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
