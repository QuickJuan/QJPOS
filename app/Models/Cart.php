<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Cart extends Model
{
    protected $fillable = [
        'customer_id',
        'cashier_id',
        'cashier_session_id',
        'table_room_id',
        'discount_id',
        'coupon_id',
        'coupon_code',
        'total_amount',
        'total_discount',
        'item_discount',
        'total_due',
        'amount_tendered',
        'session_id',
        'notes',
        'meta_data',
    ];

    protected $casts = [
        'meta_data' => 'array',
    ];

    // SCOPES
    public function scopeAuthCashier(Builder $query)
    {
        return $query->where('cashier_id', Auth::id());
    }

    public function scopeCashierOpenSession(Builder $query, int $cashierSessionId)
    {
        if ($cashierSessionId) {
            return $query->where('cashier_session_id', $cashierSessionId);
        }

        return $query;
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function cashierSession(): BelongsTo
    {
        return $this->belongsTo(CashierSession::class, 'cashier_session_id');
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(CouponCode::class, 'coupon_id');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class)->isVoid(false);
    }

    public function tableRoom(): BelongsTo
    {
        return $this->belongsTo(TableRoom::class);
    }
}
