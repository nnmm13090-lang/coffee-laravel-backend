<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'name', 'description', 'price', 'image',
        'rating_avg', 'sold_count', 'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'rating_avg' => 'decimal:1',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function favoredBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePopular(Builder $query): Builder
    {
        return $query->orderByDesc('rating_avg')->orderByDesc('sold_count');
    }

    public function scopeBestSelling(Builder $query): Builder
    {
        return $query->orderByDesc('sold_count');
    }

    /** Adds an `is_favorite` boolean for the given user (missing for guests). */
    public function scopeWithFavoriteFlag(Builder $query, ?int $userId): Builder
    {
        if (! $userId) {
            return $query;
        }

        return $query->withExists([
            'favoredBy as is_favorite' => fn ($q) => $q->where('users.id', $userId),
        ]);
    }
}
