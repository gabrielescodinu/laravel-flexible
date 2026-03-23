<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'description',
        'featured_image',
        'price',
        'status',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getPublicUrlAttribute(): string
    {
        return route('products.show', $this->slug);
    }
}
