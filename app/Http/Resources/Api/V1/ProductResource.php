<?php

namespace App\Http\Resources\Api\V1;

use App\Support\ImageUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => (float) $this->price,
            'rating' => (float) $this->rating_avg,
            'sold_count' => $this->sold_count,
            'image' => ImageUrl::make($this->image),
            'is_active' => (bool) $this->is_active,
            'is_favorite' => (bool) ($this->is_favorite ?? false),
            'category' => new CategoryResource($this->whenLoaded('category')),
        ];
    }
}
