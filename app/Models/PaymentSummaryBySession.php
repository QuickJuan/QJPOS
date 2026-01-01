<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentSummaryBySession extends Model
{
    /**
     * The table associated with the model (view).
     */
    protected $table = 'payment_summary_by_session_view';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The primary key is not auto-incrementing as this is a view.
     */
    public $incrementing = false;

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'payment_count' => 'integer',
        'total_amount' => 'decimal:2',
        'total_amount_in_payment_currency' => 'decimal:2',
        'total_change_amount' => 'decimal:2',
        'first_payment_at' => 'datetime',
        'last_payment_at' => 'datetime',
    ];

    /**
     * Get the cashier session that this summary belongs to.
     */
    public function cashierSession(): BelongsTo
    {
        return $this->belongsTo(CashierSession::class, 'cashier_session_id');
    }

    /**
     * Get the payment method.
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }
}
