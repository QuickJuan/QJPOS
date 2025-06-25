<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponUsage extends Model
{
    protected $fillable = [
        'coupon_id',
        'user_id',
        'order_number',
        'discount_amount',
        'session_id',
        'ip_address',
        'cart_data',
    ];

    protected $casts = [
        'cart_data' => 'array',
    ];

    /**
     * Get the coupon that was used
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(CouponCode::class);
    }

    /**
     * Get the user who used the coupon
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Convert discount amount from cents to dollars for display
     */
    protected function displayDiscountAmount(): Attribute
    {
        return Attribute::make(
            get: fn() => '$' . number_format($this->discount_amount / 100, 2)
        );
    }
}
