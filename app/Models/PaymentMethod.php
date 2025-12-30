<?php

namespace App\Models;

use App\Enums\PaymentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    protected $fillable = [
        'payment_type',
        'name',
        'currency_code',
        'currency_name',
        'symbol',
        'exchange_rate',
        'is_default_cash',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'payment_type' => PaymentType::class,
        'is_active' => 'boolean',
        'is_default_cash' => 'boolean',
        'exchange_rate' => 'decimal:4',
        'sort_order' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // When saving/updating a payment method
        static::saving(function ($paymentMethod) {
            // Only one payment method can be default cash
            if ($paymentMethod->is_default_cash && $paymentMethod->payment_type === PaymentType::CASH) {
                static::where('id', '!=', $paymentMethod->id)
                    ->where('payment_type', PaymentType::CASH)
                    ->update(['is_default_cash' => false]);
            }
        });
    }

    /**
     * Scope to get active payment methods
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get payment methods by type
     */
    public function scopeByType($query, PaymentType $type)
    {
        return $query->where('payment_type', $type);
    }

    /**
     * Scope to get default cash payment method
     */
    public function scopeDefaultCash($query)
    {
        return $query->where('payment_type', PaymentType::CASH)
            ->where('is_default_cash', true);
    }

    /**
     * Scope to get cash payment methods
     */
    public function scopeCash($query)
    {
        return $query->where('payment_type', PaymentType::CASH);
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Get payments made with this method
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get denominations for this payment method
     */
    public function denominations(): HasMany
    {
        return $this->hasMany(CurrencyDenomination::class);
    }

    /**
     * Check if this is a cash payment method
     */
    public function isCash(): bool
    {
        return $this->payment_type === PaymentType::CASH;
    }
}

