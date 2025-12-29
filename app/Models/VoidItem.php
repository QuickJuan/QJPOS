<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoidItem extends Model
{
    protected $fillable = [
        'parent_id',
        'product_id',
        'product_type',
        'description',
        'product_packaging_id',
        'quantity',
        'price',
        'amount',
        'order_type',
        'sub_total',
        'void_reason',
        'is_served',
        'served_by',
        'voided_by',
        'cashier_id',
        'cashier_session_id',
        'serving_number',
        'placed_order_time',
        'voided_at',
        'batch_number',
    ];

    protected $casts = [
        'serving_number'     => 'integer',
        'placed_order_time'  => 'datetime',
        'voided_at'          => 'datetime',
        'is_served'          => 'boolean',
    ];

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

    public function server(): BelongsTo
    {
        return $this->belongsTo(User::class, 'served_by');
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function cashierSession(): BelongsTo
    {
        return $this->belongsTo(\App\Models\CashierSession::class, 'cashier_session_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(VoidItem::class, 'parent_id');
    }
}
