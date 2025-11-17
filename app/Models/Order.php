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
        'cashier_id',
        'cashier_session_id',
        'table_room_id',
        'notes',
        'meta_data',
        'status',
    ];

    protected $casts = [
        'meta_data' => 'array',
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

    public function scopeSearch(Builder $query, string $keyword)
    {
        if ($keyword) {
            return $query->when($keyword, function ($query) use ($keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('id', 'like', "%{$keyword}%")
                        ->orWhereHas('cashier', function ($q) use ($keyword) {
                            $q->where('name', 'like', "%{$keyword}%");
                        })
                        ->orWhereHas('tableRoom', function ($q) use ($keyword) {
                            $q->where('customer_name', 'like', "%{$keyword}%");
                        });
                });
            });
        }

        return $query;
    }

    public function scopeDateFromFilter(Builder $query, string $dateFrom): Builder
    {
        if ($dateFrom) {
            return $query->when($dateFrom, fn($query) => $query->whereDate('created_at', '>=', $dateFrom));

        }

        return $query;
    }

    public function scopeDateToFilter(Builder $query, string $dateTo): Builder
    {
        if ($dateTo) {
            return $query->when($dateTo, fn($query) => $query->whereDate('created_at', '>=', $dateTo));

        }

        return $query;
    }

    public function scopeCashier(Builder $query, int $cashierId): Builder
    {
        if ($cashierId) {
            return $query->where('cashier_id', $cashierId);
        }

        return $query;
    }

    public function scopeStatus(Builder $query, string $status): Builder
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

    public function cashierSession(): BelongsTo
    {
        return $this->belongsTo(CashierSession::class, 'cashier_session_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class)->isVoid(false);
    }

    public function tableRoom(): BelongsTo
    {
        return $this->belongsTo(TableRoom::class);
    }
}
