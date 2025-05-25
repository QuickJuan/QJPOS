<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Helper\MediaPathGenerator;

class Product extends Model  implements HasMedia
{
    //
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
            ->singleFile()
            ->usePathGenerator(new MediaPathGenerator());

        $this->addMediaCollection('product_images')
            ->usePathGenerator(new MediaPathGenerator());
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
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
}
