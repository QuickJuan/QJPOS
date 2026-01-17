<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAddOn extends Model
{
    protected $table = 'product_add_ons';

    protected $fillable = [
        'product_id',
        'product_addon_id',
        'product_packaging_id',
        'add_on_price',
    ];

    protected $casts = [
        'add_on_price' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function addonProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_addon_id');
    }

    public function productPackaging(): BelongsTo
    {
        return $this->belongsTo(ProductPackaging::class, 'product_packaging_id');
    }
}
