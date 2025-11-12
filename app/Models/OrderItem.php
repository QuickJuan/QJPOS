<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
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
        'quantity'         => 'decimal:2',
        'price'            => 'decimal:2',
        'amount'           => 'decimal:2',
        'discount'         => 'decimal:2',
        'sub_total'        => 'decimal:2',
        'meta_data'        => 'array',
    ];

    // SCOPES
    public function scopeIsVoid(Builder $query, bool $condition = false)
    {
        return $query->where('is_void', $condition);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
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
}
