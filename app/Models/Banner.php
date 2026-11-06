<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'title', 'subtitle', 'image', 'button_text', 'action_url', 'sort_order', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
