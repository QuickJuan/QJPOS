<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    protected $fillable = [
        'code',
        'name',
        'symbol',
        'exchange_rate',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'exchange_rate' => 'decimal:4',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // When saving a currency as default, unset other defaults
        static::saving(function ($currency) {
            if ($currency->is_default) {
                // Set exchange rate to 1 for default currency
                $currency->exchange_rate = 1.0000;

                // Unset other default currencies
                static::where('id', '!=', $currency->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });
    }

    /**
     * Scope to get the default currency
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope to get active currencies
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Convert amount from this currency to another currency
     */
    public function convertTo(Currency $targetCurrency, float $amount): float
    {
        // If same currency, no conversion needed
        if ($this->id === $targetCurrency->id) {
            return $amount;
        }

        // Convert to default currency first, then to target currency
        // Formula: amount * (this_rate / target_rate)
        return $amount * ($this->exchange_rate / $targetCurrency->exchange_rate);
    }

    /**
     * Get payments made in this currency
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
