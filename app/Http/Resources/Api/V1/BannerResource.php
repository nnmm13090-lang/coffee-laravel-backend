<?php

namespace App\Http\Resources\Api\V1;

use App\Support\ImageUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'image' => ImageUrl::make($this->image),
            'button_text' => $this->button_text,
            'action_url' => $this->action_url,
            'sort_order' => $this->sort_order,
            'is_active' => (bool) $this->is_active,
        ];
    }
}
