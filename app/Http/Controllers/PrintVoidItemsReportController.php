<?php

namespace App\Http\Controllers;

use App\Models\VoidItem;
use App\Models\User;
use App\Settings\GeneralSettings;
use Carbon\Carbon;

class PrintVoidItemsReportController extends Controller
{
    public function __invoke(GeneralSettings $settings)
    {
        $cashierId = request('cashier_id');
        $from      = request('from')  ? Carbon::parse(request('from'))  : null;
        $until     = request('until') ? Carbon::parse(request('until')) : null;

        $items = VoidItem::query()
            ->with(['cashier', 'product'])
            ->when($cashierId, fn ($q) => $q->where('cashier_id', $cashierId))
            ->when($from,  fn ($q) => $q->whereDate('voided_at', '>=', $from))
            ->when($until, fn ($q) => $q->whereDate('voided_at', '<=', $until))
            ->orderByDesc('voided_at')
            ->get();

        $filterSummary = array_filter([
            'Cashier' => $cashierId ? User::find($cashierId)?->name : null,
            'From'    => $from  ? $from->format('M d, Y') : null,
            'Until'   => $until ? $until->format('M d, Y') : null,
        ]);

        return view('reports.void-items-report-print', [
            'items'         => $items,
            'generatedAt'   => now()->format('F d, Y h:i A'),
            'companyName'   => $settings->company_name ?? 'QuickJuan POS',
            'companyAddress'=> $settings->company_address,
            'filterSummary' => $filterSummary,
            'totalQty'      => $items->sum('quantity'),
            'totalAmount'   => $items->sum('sub_total'),
        ]);
    }
}
