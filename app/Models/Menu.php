<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    public const PRIMARY_LOCATION = 'primary';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'location',
        'logo_url',
        'logo_alt',
        'show_search',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'show_search' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order')->orderBy('id');
    }
}
