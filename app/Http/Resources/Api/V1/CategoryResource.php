<?php

namespace App\Http\Resources\Api\V1;

use App\Support\ImageUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'icon' => ImageUrl::make($this->icon),
        ];
    }
}
