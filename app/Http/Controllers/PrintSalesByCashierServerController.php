<?php

namespace App\Http\Controllers;

use App\Models\SalesByCashierServer;
use App\Models\Branch;
use App\Models\User;
use App\Settings\GeneralSettings;
use Carbon\Carbon;

class PrintSalesByCashierServerController extends Controller
{
    public function __invoke(GeneralSettings $settings)
    {
        $branchId  = request('branch_id');
        $serverId  = request('server_id');
        $cashierId = request('cashier_id');
        $from      = request('from')  ? Carbon::parse(request('from'))  : null;
        $until     = request('until') ? Carbon::parse(request('until')) : null;

        $items = SalesByCashierServer::query()
            ->with('branch')
            ->when($branchId,  fn ($q) => $q->where('branch_id', $branchId))
            ->when($serverId,  fn ($q) => $q->where('server_id', $serverId))
            ->when($cashierId, fn ($q) => $q->where('cashier_id', $cashierId))
            ->when($from,  fn ($q) => $q->whereDate('sale_date', '>=', $from))
            ->when($until, fn ($q) => $q->whereDate('sale_date', '<=', $until))
            ->orderByDesc('sale_date')
            ->get();

        $filterSummary = array_filter([
            'Branch'  => $branchId  ? Branch::find($branchId)?->name : null,
            'Server'  => $serverId  ? User::find($serverId)?->name   : null,
            'Cashier' => $cashierId ? User::find($cashierId)?->name  : null,
            'From'    => $from  ? $from->format('M d, Y')  : null,
            'Until'   => $until ? $until->format('M d, Y') : null,
        ]);

        return view('reports.sales-by-cashier-server-print', [
            'items'          => $items,
            'generatedAt'    => now()->format('F d, Y h:i A'),
            'companyName'    => $settings->company_name ?? 'QuickJuan POS',
            'companyAddress' => $settings->company_address,
            'filterSummary'  => $filterSummary,
            'totalGross'     => $items->sum('gross'),
            'totalDiscount'  => $items->sum('discount_amount'),
            'totalSubTotal'  => $items->sum('sub_total'),
        ]);
    }
}
