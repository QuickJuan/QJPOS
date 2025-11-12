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
    ];

    protected $casts = [
        'meta_data' => 'array',
    ];

    // SCOPES
    public function scopeAuthCashier(Builder $query)
    {
       return  $query->where('cashier_id', Auth::id());
    }

    public function scopeCashierSession(Builder $query, $cashierSessionId)
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
