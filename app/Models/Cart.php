<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    protected $fillable = [
        'cashier_id',
        'cashier_session_id',
        'session_id',
        'notes',
        'meta_data',
    ];

    protected $casts = [
        'meta_data' => 'array',
    ];

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function cashierSession(): BelongsTo
    {
        return $this->belongsTo(CashierSession::class, 'cashier_session_id');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }
}
