<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $value = rtrim(rtrim((string) $this->value, '0'), '.');
        $off = $this->type === 'percent' ? "{$value}%" : '$'.$value;

        return [
            'id' => $this->id,
            'code' => $this->code,
            'type' => $this->type,
            'value' => (float) $this->value,
            'first_order_only' => $this->first_order_only,
            'is_active' => (bool) $this->is_active,
            'title' => "Get {$off} OFF".($this->first_order_only ? ' on your first order' : ''),
            'expires_at' => $this->expires_at?->toIso8601String(),
        ];
    }
}
