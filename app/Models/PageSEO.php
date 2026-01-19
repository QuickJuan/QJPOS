<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageSEO extends Model
{
    use HasFactory;

    protected $table = 'page_seos';

    protected $fillable = [
        'page_id',
        'meta_title',
        'meta_description',
        'focus_keywords',
        'meta_robots',
        'og_title',
        'og_description',
        'og_image',
        'twitter_card',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        'schema_type',
        'schema_json',
        'canonical_url',
    ];

    protected $casts = [
        'schema_json' => 'array',
    ];

    /**
     * Get the page this SEO data belongs to.
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
