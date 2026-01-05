<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointsTransaction extends Model
{
    protected $fillable = [
        'customer_e_wallet_id',
        'order_id',
        'transaction_type',
        'points',
        'balance_before',
        'balance_after',
        'reference_number',
        'description',
        'meta_data',
        'processed_by',
    ];

    protected $casts = [
        'points' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'meta_data' => 'array',
    ];

    public function eWallet(): BelongsTo
    {
        return $this->belongsTo(CustomerEWallet::class, 'customer_e_wallet_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Scope to filter by transaction type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('transaction_type', $type);
    }
}
