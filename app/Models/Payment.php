<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'payment_method_id',
        'currency_id',
        'amount',
        'amount_in_payment_currency',
        'exchange_rate',
        'change_amount',
        'reference_number',
        'payment_details',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_in_payment_currency' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'change_amount' => 'decimal:2',
        'payment_details' => 'array',
    ];

    /**
     * Get the order this payment belongs to
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the payment method used
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Get the currency used for this payment (mainly for cash)
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Calculate change amount based on amount paid and order total
     */
    public function calculateChange(float $orderTotal): float
    {
        return max(0, $this->amount - $orderTotal);
    }
}
