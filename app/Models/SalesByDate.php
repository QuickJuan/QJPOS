<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesByDate extends Model
{
    protected $table = 'sales_by_date_view';
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
        'sold',
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
        'sold' => 'integer',
        'gross' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'sub_total' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
