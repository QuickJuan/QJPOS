<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'uuid',
        'barcode',
        'name',
        'receipt_alias',
        'description',
        'category_id',
        'brand_id',
        'preparation_location_id',
        'product_type',
        'is_active',
        'with_expiration',
        'with_serial_number',
        'sellable',
        'vat_type',
        'vat_inclusive',
        'vat_rate',
        'track_inventory',
        'total_onhand',
        'minimum_stock_level',
        'maximum_stock_level',
        'multiple_packaging',
        'price',
        'unit_measure',
    ];

    protected $casts = [
        'is_active'          => 'boolean',
        'with_expiration'    => 'boolean',
        'with_serial_number' => 'boolean',
        'sellable'           => 'boolean',
        'vat_inclusive'      => 'boolean',
        'track_inventory'    => 'boolean',
        'multiple_packaging' => 'boolean',
    ];

    protected $appends = [
        'featured_image_url',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_image')
            ->singleFile();
        // ->usePathGenerator(new MediaPathGenerator());

        $this->addMediaCollection('product_images');
        // ->usePathGenerator(new MediaPathGenerator());
    }

    public function getFeaturedImageUrlAttribute(): string
    {
        return $this->getFirstMediaUrl('featured_image');
    }

    public function getProductImagesUrlsAttribute(): array
    {
        return $this->getMedia('product_images')->map(function ($media) {
            return $media->getUrl();
        })->toArray();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
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

    public function options(): HasMany
    {
        return $this->hasMany(Option::class);
    }

    public function optionItems(): HasMany
    {
        return $this->hasMany(OptionItem::class);
    }

    public function preparationLocation(): BelongsTo
    {
        return $this->belongsTo(PreparationLocation::class);
    }

    public function recipeInventories(): BelongsToMany
    {
        return $this->belongsToMany(Inventory::class, 'inventory_product')
            ->withPivot(['quantity', 'unit_measure', 'unit_type', 'unit_reference_id'])
            ->withTimestamps();
    }

    public function inventoryRecipes(): HasMany
    {
        return $this->hasMany(ProductInventory::class, 'product_id');
    }
}
