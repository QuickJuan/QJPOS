<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Order extends Model
{
    protected $fillable = [
        'customer_id',
        'cashier_id',
        'cashier_session_id',
        'table_room_id',
        'discount_id',
        'coupon_id',
        'coupon_code',
        'invoice_no',
        'total_amount',
        'total_discount',
        'item_discount',
        'total_due',
        'amount_tendered',
        'vatable_sales',
        'vat_amount',
        'vat_exempt_sales',
        'zero_rated_sales',
        'non_vat',
        'notes',
        'meta_data',
        'status',
    ];

    protected $casts = [
        'meta_data' => 'array',
    ];

    protected $appends = [
        'payment_info',
    ];

    // SCOPES
    public function scopeAuthCashier(Builder $query): Builder
    {
        return $query->where('cashier_id', Auth::id());
    }

    public function scopeCashierSession(Builder $query, $cashierSessionId): Builder
    {
        if ($cashierSessionId) {
            $query->where('cashier_session_id', $cashierSessionId);
        }

        return $query;
    }

    public function scopeSearch(Builder $query, ?string $keyword)
    {
        if ($keyword) {
            return $query->when($keyword, function ($query) use ($keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('id', 'like', "%{$keyword}%") // receipt number but since we don't have receipt_number column, set the id temporarily
                        ->orWhereHas('cashier', function ($q) use ($keyword) {
                            $q->whereLike('name', "%$keyword%");
                        })
                        ->orWhereHas('tableRoom', function ($q) use ($keyword) {
                            $q->whereLike('customer_name', "%$keyword%")
                                ->orWhereLike('name', "%$keyword%");
                        });
                });
            });
        }

        return $query;
    }

    public function scopeDateFromFilter(Builder $query, ?string $dateFrom): Builder
    {
        if ($dateFrom) {
            return $query->when($dateFrom, fn($query) => $query->whereDate('created_at', '>=', $dateFrom));

        }

        return $query;
    }

    public function scopeDateToFilter(Builder $query, ?string $dateTo): Builder
    {
        if ($dateTo) {
            return $query->when($dateTo, fn($query) => $query->whereDate('created_at', '<=', $dateTo));
        }

        return $query;
    }

    public function scopeCashier(Builder $query, ?int $cashierId): Builder
    {
        if ($cashierId) {
            return $query->where('cashier_id', $cashierId);
        }

        return $query;
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        if ($status) {
            return $query->where('status', $status);
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

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class)->isVoid(false);
    }

    public function tableRoom(): BelongsTo
    {
        return $this->belongsTo(TableRoom::class);
    }

    public function getPaymentInfoAttribute()
    {
        return $this->meta_data['payment_info'] ?? null;
    }
}
