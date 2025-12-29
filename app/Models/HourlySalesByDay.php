<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HourlySalesByDay extends Model
{
    protected $table = 'hourly_sales_by_day_view';
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'id',
        'branch_id',
        'year_no',
        'month_no',
        'sale_hour',
        'monday_sales',
        'tuesday_sales',
        'wednesday_sales',
        'thursday_sales',
        'friday_sales',
        'saturday_sales',
        'sunday_sales',
        'total_sales',
    ];

    protected $casts = [
        'year_no' => 'integer',
        'month_no' => 'integer',
        'sale_hour' => 'integer',
        'monday_sales' => 'decimal:2',
        'tuesday_sales' => 'decimal:2',
        'wednesday_sales' => 'decimal:2',
        'thursday_sales' => 'decimal:2',
        'friday_sales' => 'decimal:2',
        'saturday_sales' => 'decimal:2',
        'sunday_sales' => 'decimal:2',
        'total_sales' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
