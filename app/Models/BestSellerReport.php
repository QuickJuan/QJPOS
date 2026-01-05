<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BestSellerReport extends Model
{
    protected $table = 'best_seller_report_view';
    protected $primaryKey = 'id';
    protected $keyType = 'int';

    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'id',
        'branch_id',
        'year_no',
        'month_no',
        'product_id',
        'branch_name',
        'product_name',
        'qty_sold',
        'net_sales',
    ];

    protected $casts = [
        'branch_id' => 'integer',
        'year_no' => 'integer',
        'month_no' => 'integer',
        'product_id' => 'integer',
        'qty_sold' => 'integer',
        'net_sales' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
