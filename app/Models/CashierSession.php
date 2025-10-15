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
    ];

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }
}
