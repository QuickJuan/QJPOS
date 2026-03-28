<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NavigationItem extends Model
{
    protected $fillable = [
        'label',
        'url',
        'page_id',
        'target',
        'order',
        'parent_id',
        'is_active',
        'icon',
        'auth_only',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'auth_only' => 'boolean',
        'order' => 'integer',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(NavigationItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(NavigationItem::class, 'parent_id')->orderBy('order');
    }

    public function getUrlAttribute($value): string
    {
        // If page_id is set, use the page's full URL path
        if ($this->page_id && $this->page) {
            $path = $this->page->getFullUrlPath();
            // Return just '/' for landing pages (empty path), otherwise add leading slash
            return $path === '' ? '/' : '/' . $path;
        }

        return $value ?: '#';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRootItems($query)
    {
        return $query->whereNull('parent_id')->orderBy('order');
    }
}
