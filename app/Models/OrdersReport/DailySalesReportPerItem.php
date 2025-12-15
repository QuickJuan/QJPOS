<?php
namespace App\Models\OrdersReport;

use Illuminate\Database\Eloquent\Model;

class DailySalesReportPerItem extends Model
{
    protected $table      = 'daily_sales_report_per_item_view';
    public $incrementing  = false;
    public $timestamps    = false;
    protected $primaryKey = 'id';

    protected $fillable = [
        'order_date',
        'item_name',
        'quantity',
        'price',
        'gross_sales',
        'discount',
        'net_sales',
        'category_id',
        'brand_id',
    ];

    protected $hidden = [
        'id',
        'category_id',
        'brand_id',
    ];
}
