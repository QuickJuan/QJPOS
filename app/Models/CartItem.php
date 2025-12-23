<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CartItem extends Model
{
    protected $fillable = [
        'parent_id',
        'cart_id',
        'product_id',
        'product_type',
        'description',
        'product_packaging_id',
        'quantity',
        'price',
        'discount_amount',
        'vatable_sales',
        'vat_exempt_sales',
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
    ];

    protected $casts = [
        'selected_options' => 'array',
        'meta_data'        => 'array',
        'serving_number'   => 'integer',
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

    public function itemDiscount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    public function servedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'served_by');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function childrenRecursive(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->with([
                'childrenRecursive',
                'product',
                'productPackaging',
                'product.preparationLocation',
            ]);
    }

    public function selectedOptions()
    {
        return $this->belongsToMany(OptionItem::class, 'cart_item_options', 'cart_item_id', 'option_item_id');
    }
}
