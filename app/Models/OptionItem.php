<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OptionItem extends Model
{
    protected $fillable = [
        'product_option_id',
        'product_packaging_id',
        'additional_price',
    ];

    public function productOption(): BelongsTo
    {
        return $this->belongsTo(ProductOption::class);
    }

    public function productPackaging(): BelongsTo
    {
        return $this->belongsTo(ProductPackaging::class);
    }

}
