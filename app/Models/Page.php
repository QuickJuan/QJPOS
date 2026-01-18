<?php

namespace App\Models;

use App\Enums\PageType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Page extends Model
{
    use HasFactory, SoftDeletes;

    protected static function boot()
    {
        parent::boot();

        /**
         * When a page is permanently deleted (force delete),
         * cascade delete all related blocks and SEO data
         */
        static::forceDeleted(function ($page) {
            $page->blocks()->forceDelete();
            $page->seo()->forceDelete();
            $page->deleteFeaturedMedia();
        });

        /**
         * When a page is soft deleted, also soft delete related blocks
         * (SEO is handled by cascadeOnDelete in database)
         */
        static::deleting(function ($page) {
            if (!$page->isForceDeleting()) {
                $page->blocks()->delete();
            }
        });

        /**
         * When a page is restored, restore all its blocks
         */
        static::restored(function ($page) {
            $page->blocks()->restore();
        });
    }

    /**
     * Delete featured media files
     */
    private function deleteFeaturedMedia(): void
    {
        if ($this->featured_image && Storage::disk('public')->exists($this->featured_image)) {
            Storage::disk('public')->delete($this->featured_image);
        }
    }

    protected $fillable = [
        'title',
        'hide_title',
        'slug',
        'url_prefix',
        'page_type',
        'featured_image',
        'featured_video',
        'content_json',
        'status',
        'created_by',
        'published_at',
        'scheduled_at',
        'view_count',
    ];

    protected $casts = [
        'content_json' => 'array',
        'page_type' => PageType::class,
        'hide_title' => 'boolean',
        'published_at' => 'datetime',
        'scheduled_at' => 'datetime',
    ];

    /**
     * Get the page blocks.
     */
    public function blocks(): HasMany
    {
        return $this->hasMany(PageBlock::class)->orderBy('order');
    }

    /**
     * Get the page SEO data.
     */
    public function seo(): HasOne
    {
        return $this->hasOne(PageSEO::class);
    }

    /**
     * Get the user who created the page.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get only published pages.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope to get only draft pages.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope to get only archived pages.
     */
    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    /**
     * Check if page is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Publish the page.
     */
    public function publish(): void
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    /**
     * Unpublish the page.
     */
    public function unpublish(): void
    {
        $this->update([
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    /**
     * Get the full URL path for this page
     */
    public function getFullUrlPath(): ?string
    {
        // Landing pages render on root (empty string for root URL)
        if ($this->page_type === PageType::LANDING_PAGE) {
            return '';
        }

        // Pages without slugs don't have a URL
        if (!$this->slug) {
            return null;
        }

        if ($this->url_prefix) {
            return "{$this->url_prefix}/{$this->slug}";
        }
        return $this->slug;
    }
}
