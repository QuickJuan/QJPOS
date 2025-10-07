<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'uuid',
        'name',
        'receipt_alias',
        'description',
        'category_id',
        'brand_id',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_image')
            ->singleFile();
        // ->usePathGenerator(new MediaPathGenerator());

        $this->addMediaCollection('product_images');
        // ->usePathGenerator(new MediaPathGenerator());
    }

    public function getFeaturedImageUrlAttribute()
    {
        return $this->getFirstMediaUrl('featured_image');
    }

    public function getProductImagesUrlsAttribute()
    {
        return $this->getMedia('product_images')->map(function ($media) {
            return $media->getUrl();
        })->toArray();
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function productPackagings(): HasMany
    {
        return $this->hasMany(ProductPackaging::class);
    }

    public function options(): BelongsToMany
    {
        return $this->belongsToMany(Option::class, 'option_product')->withTimestamps();
    }

    public function optionItems(): HasMany
    {
        return $this->hasMany(OptionItem::class);
    }
}
