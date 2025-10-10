<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
