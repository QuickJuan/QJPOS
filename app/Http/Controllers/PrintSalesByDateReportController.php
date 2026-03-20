<?php

namespace App\Http\Controllers;

use App\Models\SalesByDate;
use App\Models\Branch;
use App\Settings\GeneralSettings;
use Carbon\Carbon;

class PrintSalesByDateReportController extends Controller
{
    public function __invoke(GeneralSettings $settings)
    {
        $branchId = request('branch_id');
        $from     = request('from')  ? Carbon::parse(request('from'))  : null;
        $until    = request('until') ? Carbon::parse(request('until')) : null;

        $items = SalesByDate::query()
            ->with('branch')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($from,  fn ($q) => $q->whereDate('sale_date', '>=', $from))
            ->when($until, fn ($q) => $q->whereDate('sale_date', '<=', $until))
            ->orderBy('sale_date', 'desc')
            ->get();

        $filterSummary = array_filter([
            'Branch' => $branchId ? Branch::find($branchId)?->name : null,
            'From'   => $from  ? $from->format('M d, Y') : null,
            'Until'  => $until ? $until->format('M d, Y') : null,
        ]);

        return view('reports.sales-by-date-report-print', [
            'items'         => $items,
            'generatedAt'   => now()->format('F d, Y h:i A'),
            'companyName'   => $settings->company_name ?? 'QuickJuan POS',
            'companyAddress'=> $settings->company_address,
            'filterSummary' => $filterSummary,
            'totalSold'     => $items->sum('sold'),
            'totalGross'    => $items->sum('gross'),
            'totalDiscount' => $items->sum('discount_amount'),
            'totalSubTotal' => $items->sum('sub_total'),
        ]);
    }
}
