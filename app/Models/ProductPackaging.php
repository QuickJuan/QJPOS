<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ProductPackaging extends Model implements HasMedia
{
    use InteractsWithMedia;
    
    protected $fillable = [
        'product_id',
        'cost',
        'price',
        'unit_measure',
        'qty',
        'featured_image',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
