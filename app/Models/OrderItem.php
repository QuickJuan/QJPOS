<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'parent_id',
        'product_id',
        'product_packaging_id',
        'quantity',
        'price',
        'discount_amount',
        'item_discount',
        'vatable_sales',
        'vat_exempt_sales',
        'zero_rated_sales',
        'vat_amount',
        'non_vat_sales',
        'less_tax',
        'amount',
        'order_type',
        'discount',
        'discount_id',
        'coupon_code',
        'sub_total',
        'is_served',
        'placed_order',
        'is_void',
        'reason',
        'notes',
        'meta_data',
        'served_by',
        'serving_number',
        'placed_order_time',
        'served_time',
        'batch_number',
    ];

    protected $casts = [
        'quantity'         => 'decimal:2',
        'price'            => 'decimal:2',
        'amount'           => 'decimal:2',
        'discount'         => 'decimal:2',
        'sub_total'        => 'decimal:2',
        'meta_data'        => 'array',
        'serving_number'   => 'integer',
    ];

    // SCOPES
    public function scopeIsVoid(Builder $query, bool $condition = false)
    {
        $query->where('is_void', $condition);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
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
