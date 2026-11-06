<?php

namespace App\Http\Resources\Api\V1;

use App\Support\ImageUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar' => ImageUrl::make($this->avatar),
            'roles' => $this->getRoleNames()->values(),
        ];
    }
}
