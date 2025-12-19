<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Option extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'option_name',
        'product_id',
        'max_quantity',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_image');
    }

    public function optionItems(): HasMany
    {
        return $this->hasMany(OptionItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
