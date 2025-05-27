<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductOption extends Model
{
    protected $fillable = [
        'product_packaging_id',
        'name',
        'qty',
        'is_default',
        'featured_image',
    ];

    public function productPackaging(): BelongsTo
    {
        return $this->belongsTo(ProductPackaging::class, 'product_packaging_id');
    }

    public function getFeaturedImageUrlAttribute()
    {
        return $this->getFirstMediaUrl('featured_image');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_image')
            ->singleFile();
            // ->usePathGenerator(new MediaPathGenerator());
    }

    public function getFeaturedImageUrl()
    {
        return $this->getFirstMediaUrl('featured_image');
    }


    public function optionItems()
    {
        return $this->hasMany(OptionItem::class, 'product_option_id');
    }

    public function getOptionItems()
    {
        return $this->optionItems()->get();
    }


}
