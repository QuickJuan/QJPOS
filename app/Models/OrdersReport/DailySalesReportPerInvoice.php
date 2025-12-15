<?php
namespace App\Models\OrdersReport;

use Illuminate\Database\Eloquent\Model;

class DailySalesReportPerInvoice extends Model
{
    protected $table      = 'daily_sales_report_per_invoice_view';
    public $incrementing  = false;
    public $timestamps    = false;
    protected $primaryKey = 'id';

    protected $fillable = [
        'order_date',
        'cashier_shift_number',
        'customer',
        'invoice_number',
        'gross_sales',
        'discount',
        'net_sales',
        'status',
    ];

    protected $hidden = [
        'id',
    ];
}
