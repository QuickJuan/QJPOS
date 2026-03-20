<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Branch;
use App\Settings\GeneralSettings;
use Carbon\Carbon;

class PrintDetailedSalesItemReportController extends Controller
{
    public function __invoke(GeneralSettings $settings)
    {
        $branchId = request('branch_id');
        $status   = request('status');
        $from     = request('from')  ? Carbon::parse(request('from'))  : null;
        $until    = request('until') ? Carbon::parse(request('until')) : null;

        $items = OrderItem::query()
            ->with(['product', 'order.branch', 'order.cashier', 'order.customer'])
            ->whereHas('order')
            ->when($branchId, fn ($q) => $q->whereHas('order', fn ($oq) => $oq->where('branch_id', $branchId)))
            ->when($status,   fn ($q) => $q->whereHas('order', fn ($oq) => $oq->where('status', $status)))
            ->when($from,  fn ($q) => $q->whereHas('order', fn ($oq) => $oq->whereDate('created_at', '>=', $from)))
            ->when($until, fn ($q) => $q->whereHas('order', fn ($oq) => $oq->whereDate('created_at', '<=', $until)))
            ->orderByDesc('order_id')
            ->orderByRaw('COALESCE(NULLIF(parent_id, 0), id)')
            ->orderByRaw('CASE WHEN parent_id IS NULL OR parent_id = 0 THEN 0 ELSE 1 END')
            ->orderBy('id')
            ->get();

        $filterSummary = array_filter([
            'Branch' => $branchId ? Branch::find($branchId)?->name : null,
            'Status' => $status   ? ucfirst($status)               : null,
            'From'   => $from  ? $from->format('M d, Y')  : null,
            'Until'  => $until ? $until->format('M d, Y') : null,
        ]);

        return view('reports.detailed-sales-item-report-print', [
            'items'          => $items,
            'generatedAt'    => now()->format('F d, Y h:i A'),
            'companyName'    => $settings->company_name ?? 'QuickJuan POS',
            'companyAddress' => $settings->company_address,
            'filterSummary'  => $filterSummary,
            'totalQty'       => $items->sum('quantity'),
            'totalAmount'    => $items->sum('amount'),
            'totalDiscount'  => $items->sum('item_discount'),
            'totalLessTax'   => $items->sum('less_tax'),
            'totalSubTotal'  => $items->sum('sub_total'),
        ]);
    }
}
