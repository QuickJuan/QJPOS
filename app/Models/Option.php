<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Option extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'option_name',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_image');
    }

    public function optionItems(): HasMany
    {
        return $this->hasMany(Option::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'option_product')->withTimestamps();
    }
}
