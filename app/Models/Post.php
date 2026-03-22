<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'author_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'status',
        'published_at',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'seo_robots',
        'seo_canonical_url',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->orderBy('name');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->where(function (Builder $builder): void {
                $builder
                    ->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function primaryCategory(): ?Category
    {
        if ($this->relationLoaded('categories')) {
            $loadedCategory = $this->categories->first();

            if ($loadedCategory !== null && filled($loadedCategory->slug)) {
                return $loadedCategory;
            }
        }

        return $this->categories()
            ->select(['categories.id', 'categories.name', 'categories.slug'])
            ->first();
    }

    public function getPublicUrlAttribute(): string
    {
        $primaryCategory = $this->primaryCategory();

        if ($primaryCategory !== null) {
            return route('articles.show.category', [
                'category' => $primaryCategory->slug,
                'post' => $this->slug,
            ]);
        }

        return route('articles.show', [
            'post' => $this->slug,
        ]);
    }
}
