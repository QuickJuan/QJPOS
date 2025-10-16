<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashierSession extends Model
{
    protected $fillable = [
        'business_date',
        'cashier_id',
        'started_time',
        'closing_time',
        'beginning_cash',
        'closing_cash',
        'total_sales',
        'check_by',
        'cash_denomination',
        'meta_data',
        'notes',
    ];

    protected $casts = [
        'business_date'     => 'datetime',
        'started_time'      => 'datetime',
        'closing_time'      => 'datetime',
        'cash_denomination' => 'array',
        'meta_data'         => 'array',
    ];

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }
}
