<?php
namespace App\Models\OrdersReport;

use Illuminate\Database\Eloquent\Model;

class BestSellerReport extends Model
{

    protected $table      = 'best_seller_report_view';
    public $incrementing  = false;
    public $timestamps    = false;
    protected $primaryKey = 'id';

    protected $fillable = [
        'order_date',
        'product',
        'qty',
        'net_sales',
    ];

    protected $hidden = [
        'id',
    ];
}
