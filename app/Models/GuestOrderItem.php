<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestOrderItem extends Model
{
    protected $fillable = [
        'guest_order_id',
        'product_id',
        'product_name',
        'price',
        'quantity',
        'subtotal',
    ];

    public function guestOrder(): BelongsTo
    {
        return $this->belongsTo(GuestOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
