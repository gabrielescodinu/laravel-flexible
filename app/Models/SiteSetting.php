<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class SiteSetting extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'value',
    ];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        if (! Schema::hasTable('site_settings')) {
            return $default;
        }

        $raw = static::query()
            ->where('key', $key)
            ->value('value');

        if ($raw === null) {
            return $default;
        }

        $decoded = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $default;
        }

        return $decoded;
    }

    public static function setValue(string $key, mixed $value): void
    {
        if (! Schema::hasTable('site_settings')) {
            return;
        }

        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );
    }

    public static function ecommerceEnabled(): bool
    {
        return (bool) static::getValue('ecommerce_enabled', false);
    }
}
