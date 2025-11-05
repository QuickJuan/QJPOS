<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'product_id',
        'product_packaging_id',
        'quantity',
        'price',
        'amount',
        'order_type',
        'discount',
        'discount_id',
        'coupon_code',
        'sub_total',
        'selected_options',
        'is_served',
        'is_void',
        'reason',
        'notes',
        'meta_data',
    ];

    protected $casts = [
        'selected_options' => 'array',
        'meta_data'        => 'array',
    ];

    // SCOPES
    public function scopeIsVoid(Builder $query, bool $condition = false)
    {
        $query->where('is_void', $condition);
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productPackaging(): BelongsTo
    {
        return $this->belongsTo(ProductPackaging::class);
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    public function selectedOptions()
    {
        return $this->belongsToMany(OptionItem::class, 'cart_item_options', 'cart_item_id', 'option_item_id');
    }
}
