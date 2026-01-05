<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerEWallet extends Model
{
    protected $fillable = [
        'customer_id',
        'balance',
        'total_loaded',
        'total_spent',
        'earned_points',
        'redeemed_points',
        'points_balance',
        'is_active',
        'last_transaction_at',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'total_loaded' => 'decimal:2',
        'total_spent' => 'decimal:2',
        'earned_points' => 'decimal:2',
        'redeemed_points' => 'decimal:2',
        'points_balance' => 'decimal:2',
        'is_active' => 'boolean',
        'last_transaction_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(EWalletTransaction::class);
    }

    public function pointsTransactions(): HasMany
    {
        return $this->hasMany(PointsTransaction::class);
    }

    /**
     * Check if e-wallet has sufficient balance
     */
    public function hasSufficientBalance(float $amount): bool
    {
        return $this->is_active && $this->balance >= $amount;
    }

    /**
     * Check if customer has sufficient points
     */
    public function hasSufficientPoints(float $points): bool
    {
        return $this->is_active && $this->points_balance >= $points;
    }

    /**
     * Get formatted balance
     */
    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->balance, 2);
    }

    /**
     * Get formatted points balance
     */
    public function getFormattedPointsBalanceAttribute(): string
    {
        return number_format($this->points_balance, 2);
    }
}
