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
        'cashier_id',
        'cashier_session_id',
        'table_room_id',
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
        $query->where('cashier_id', Auth::id());
    }

    public function scopeCashierOpenSession(Builder $query, $cashierSessionId)
    {
        if ($cashierSessionId) {
            $query->where('cashier_session_id', $cashierSessionId);
        }
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function cashierSession(): BelongsTo
    {
        return $this->belongsTo(CashierSession::class, 'cashier_session_id');
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
