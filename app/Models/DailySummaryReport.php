<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailySummaryReport extends Model
{
    protected $table = 'daily_summary_report_view';
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
        'category_id',
        'category_name',
        'brand_id',
        'brand_name',
        'product_id',
        'product_name',
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
        'category_id' => 'integer',
        'brand_id' => 'integer',
        'product_id' => 'integer',
        'sold' => 'integer',
        'gross' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'sub_total' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
