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
        'currency_id',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'payment_type' => PaymentType::class,
        'is_active' => 'boolean',
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
            // If payment type is not cash, set currency_id to default currency
            if ($paymentMethod->payment_type !== PaymentType::CASH) {
                $defaultCurrency = Currency::where('is_default', true)->first();
                if ($defaultCurrency) {
                    $paymentMethod->currency_id = $defaultCurrency->id;
                }
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
     * Get the currency for this payment method (for cash payments)
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Check if this is a cash payment method
     */
    public function isCash(): bool
    {
        return $this->payment_type === PaymentType::CASH;
    }

    /**
     * Check if this payment method requires currency
     */
    public function requiresCurrency(): bool
    {
        return $this->isCash();
    }
}
