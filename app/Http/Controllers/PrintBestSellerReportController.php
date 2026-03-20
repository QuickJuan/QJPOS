<?php

namespace App\Http\Controllers;

use App\Models\BestSellerReport;
use App\Models\Branch;
use App\Settings\GeneralSettings;

class PrintBestSellerReportController extends Controller
{
    public function __invoke(GeneralSettings $settings)
    {
        $branchId = request('branch_id');
        $yearNo   = request('year_no');
        $monthNo  = request('month_no');

        $query = BestSellerReport::query()
            ->with('branch')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($yearNo,   fn ($q) => $q->where('year_no', $yearNo))
            ->when($monthNo,  fn ($q) => $q->where('month_no', $monthNo))
            ->orderByDesc('qty_sold');

        $items = $query->get();

        $months = [
            1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',
            7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December',
        ];

        $filterSummary = array_filter([
            'Branch' => $branchId ? Branch::find($branchId)?->name : null,
            'Year'   => $yearNo,
            'Month'  => $monthNo ? ($months[$monthNo] ?? $monthNo) : null,
        ]);

        return view('reports.best-seller-report-print', [
            'items'          => $items,
            'generatedAt'    => now()->format('F d, Y h:i A'),
            'companyName'    => $settings->company_name ?? 'QuickJuan POS',
            'companyAddress' => $settings->company_address,
            'filterSummary'  => $filterSummary,
            'totalQtySold'   => $items->sum('qty_sold'),
            'totalNetSales'  => $items->sum('net_sales'),
        ]);
    }
}
