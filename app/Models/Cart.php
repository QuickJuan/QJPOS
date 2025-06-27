<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'customer_id',
        'cashier_shift_id',
        'notes',
        'meta_data',
    ];

    protected $casts = [
        'meta_data' => 'array',
    ];

    // public function customer()
    // {
    //     return $this->belongsTo(Customer::class);
    // }

    // public function cashierShift()
    // {
    //     return $this->belongsTo(CashierShift::class);
    // }
}
