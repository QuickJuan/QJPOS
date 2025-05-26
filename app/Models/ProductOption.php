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
}
