<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Group extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'featured_image',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_image');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

}
