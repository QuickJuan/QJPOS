<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Branch;
use App\Models\User;
use App\Settings\GeneralSettings;
use Carbon\Carbon;

class PrintDetailedSalesReportController extends Controller
{
    public function __invoke(GeneralSettings $settings)
    {
        $branchId  = request('branch_id');
        $cashierId = request('cashier_id');
        $status    = request('status');
        $from      = request('from')  ? Carbon::parse(request('from'))  : null;
        $until     = request('until') ? Carbon::parse(request('until')) : null;

        $orders = Order::query()
            ->with(['branch', 'cashier', 'customer', 'payments.paymentMethod'])
            ->withCount('orderItems')
            ->when($branchId,  fn ($q) => $q->where('branch_id', $branchId))
            ->when($cashierId, fn ($q) => $q->where('cashier_id', $cashierId))
            ->when($status,    fn ($q) => $q->where('status', $status))
            ->when($from,  fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($until, fn ($q) => $q->whereDate('created_at', '<=', $until))
            ->orderByDesc('created_at')
            ->get();

        $filterSummary = array_filter([
            'Branch'  => $branchId  ? Branch::find($branchId)?->name : null,
            'Cashier' => $cashierId ? User::find($cashierId)?->name   : null,
            'Status'  => $status    ? ucfirst($status)                : null,
            'From'    => $from  ? $from->format('M d, Y')  : null,
            'Until'   => $until ? $until->format('M d, Y') : null,
        ]);

        return view('reports.detailed-sales-report-print', [
            'orders'            => $orders,
            'generatedAt'       => now()->format('F d, Y h:i A'),
            'companyName'       => $settings->company_name ?? 'QuickJuan POS',
            'companyAddress'    => $settings->company_address,
            'filterSummary'     => $filterSummary,
            'totalAmount'       => $orders->sum('total_amount'),
            'totalDiscount'     => $orders->sum(fn ($o) => ($o->item_discount ?? 0) + ($o->less_tax ?? 0)),
            'totalItemDiscount' => $orders->sum('item_discount'),
            'totalLessTax'      => $orders->sum('less_tax'),
            'totalServiceCharge'=> $orders->sum('service_charge'),
            'totalDue'          => $orders->sum(fn ($o) => ($o->total_due ?? 0) + ($o->service_charge ?? 0)),
        ]);
    }
}
