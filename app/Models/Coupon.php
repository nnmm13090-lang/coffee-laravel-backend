<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = ['code', 'type', 'value', 'first_order_only', 'expires_at', 'is_active'];

    protected $casts = [
        'value' => 'decimal:2',
        'first_order_only' => 'boolean',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function scopeUsable(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }
}
