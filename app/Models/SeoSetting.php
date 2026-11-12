<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;

class SeoSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, ?string $default = null): ?string
    {
        $cacheKey = 'seo_setting_' . $key;
        try {
            return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
                $row = static::where('key', $key)->first();
                return $row ? $row->value : $default;
            });
        } catch (QueryException $e) {
            // The seo_settings table may not exist yet on deployments where
            // migrations have not run. Do not 500 the admin SEO page or the
            // public /api/seo-settings endpoint; fall back to the default.
            // Run `php artisan migrate` on the server to create the table.
            return $default;
        }
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('seo_setting_' . $key);
    }
}
