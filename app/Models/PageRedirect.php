<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PageRedirect extends Model
{
    protected $fillable = [
        'from_path',
        'to_url',
        'redirect_type',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'redirect_type' => 'integer',
    ];

    /**
     * Normalise the from_path so it always starts with a leading slash
     * and never has a trailing slash.
     */
    public function setFromPathAttribute(string $value): void
    {
        $path = '/' . ltrim(rtrim(trim($value), '/'), '/');
        $this->attributes['from_path'] = strtolower($path);
    }

    /**
     * Auto-invalidate the redirect map cache whenever a record changes.
     */
    protected static function booted(): void
    {
        $flush = fn () => Cache::forget('page_redirects_map_' . tenant('id'));

        static::saved($flush);
        static::deleted($flush);
    }

    /**
     * Return all active redirects as a path-keyed array, cached for 10 minutes.
     */
    public static function getActiveMap(): array
    {
        $key = 'page_redirects_map_' . tenant('id');

        return Cache::remember($key, 600, function () {
            return static::where('is_active', true)
                ->get(['from_path', 'to_url', 'redirect_type'])
                ->keyBy('from_path')
                ->map(fn ($r) => ['to_url' => $r->to_url, 'type' => $r->redirect_type])
                ->toArray();
        });
    }
}
