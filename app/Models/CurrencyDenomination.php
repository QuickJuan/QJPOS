<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CurrencyDenomination extends Model
{
    protected $fillable = [
        'payment_method_id',
        'value',
        'label',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Scope to only get denominations for cash payment methods
     */
    public function scopeCashOnly($query)
    {
        return $query->whereHas('paymentMethod', function ($q) {
            $q->where('payment_type', 'cash');
        });
    }
}
