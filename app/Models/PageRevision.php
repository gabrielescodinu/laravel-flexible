<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageRevision extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'page_id',
        'user_id',
        'label',
        'page_data',
        'blocks',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'page_data' => 'array',
        'blocks' => 'array',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
