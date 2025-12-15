<?php

namespace App\Models\OrdersReport;

use Illuminate\Database\Eloquent\Model;

class HourlySalesReport extends Model
{
    protected $table      = 'hourly_sales_report_view';
    public $incrementing  = false;
    public $timestamps    = false;
    protected $primaryKey = 'id';

    protected $fillable = [
        'order_date',
        'order_time',
        'item_name',
        'quantity',
        'price',
        'gross_sales',
        'discount',
        'net_sales',
        'category_id',
        'brand_id',
        'order_status'
    ];

    protected $hidden = [
        'id',
        'category_id',
        'brand_id',
    ];
}
