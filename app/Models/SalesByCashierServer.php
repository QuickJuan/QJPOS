<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesByCashierServer extends Model
{
    protected $table = 'sales_by_cashier_server_view';
    protected $primaryKey = 'id';
    protected $keyType = 'int';

    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'id',
        'branch_id',
        'sale_date',
        'year_no',
        'month_no',
        'day_no',
        'branch_name',
        'server_id',
        'server_name',
        'cashier_id',
        'cashier_name',
        'gross',
        'discount_amount',
        'sub_total',
    ];

    protected $casts = [
        'branch_id' => 'integer',
        'sale_date' => 'date',
        'year_no' => 'integer',
        'month_no' => 'integer',
        'day_no' => 'integer',
        'server_id' => 'integer',
        'cashier_id' => 'integer',
        'gross' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'sub_total' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(User::class, 'server_id');
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }
}
