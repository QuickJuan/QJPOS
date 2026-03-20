<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Branch;
use App\Settings\GeneralSettings;
use Carbon\Carbon;

class PrintRefundOrdersReportController extends Controller
{
    public function __invoke(GeneralSettings $settings)
    {
        $branchId      = request('branch_id');
        $paymentMethod = request('payment_method');
        $from          = request('from')  ? Carbon::parse(request('from'))  : null;
        $until         = request('until') ? Carbon::parse(request('until')) : null;

        $orders = Order::query()
            ->where('status', 'refund')
            ->with(['branch', 'cashier', 'customer'])
            ->when($branchId,      fn ($q) => $q->where('branch_id', $branchId))
            ->when($paymentMethod, fn ($q) => $q->where('payment_method', $paymentMethod))
            ->when($from,  fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($until, fn ($q) => $q->whereDate('created_at', '<=', $until))
            ->orderByDesc('created_at')
            ->get();

        $filterSummary = array_filter([
            'Branch'         => $branchId ? Branch::find($branchId)?->name : null,
            'Payment Method' => $paymentMethod ? ucfirst($paymentMethod) : null,
            'From'           => $from  ? $from->format('M d, Y') : null,
            'Until'          => $until ? $until->format('M d, Y') : null,
        ]);

        return view('reports.refund-orders-report-print', [
            'orders'         => $orders,
            'generatedAt'    => now()->format('F d, Y h:i A'),
            'companyName'    => $settings->company_name ?? 'QuickJuan POS',
            'companyAddress' => $settings->company_address,
            'filterSummary'  => $filterSummary,
            'totalSubtotal'  => $orders->sum('total_amount'),
            'totalDiscount'  => $orders->sum(fn ($o) => $o->item_discount + $o->less_tax),
            'totalSvcCharge' => $orders->sum('service_charge'),
            'totalDue'       => $orders->sum(fn ($o) => $o->total_due + $o->service_charge),
        ]);
    }
}
