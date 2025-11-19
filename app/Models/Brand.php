<?php
namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'slug',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($brand) {
            if (empty($brand->slug)) {
                $brand->slug = Str::slug($brand->name);
            }
        });

        static::updating(function ($brand) {
            if ($brand->isDirty('name')) {
                $brand->slug = Str::slug($brand->name);
            }
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_image');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
