<?php

namespace App\Filament\Tenant\Resources\SalesByCashierServerResource\Widgets;

use App\Models\SalesByCashierServer;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class SalesByBranchStats extends BaseWidget
{
    protected function getStats(): array
    {
        // Get sales grouped by branch
        $salesByBranch = SalesByCashierServer::select('branch_id', 'branch_name')
            ->selectRaw('SUM(sub_total) as total_sales')
            ->groupBy('branch_id', 'branch_name')
            ->get();

        $stats = [];

        foreach ($salesByBranch as $branch) {
            $stats[] = Stat::make($branch->branch_name ?? 'Unknown Branch', '₱' . number_format($branch->total_sales, 2))
                ->description('Total Sales')
                ->color('success')
                ->icon('heroicon-o-building-storefront');
        }

        // Add overall total
        $totalSales = $salesByBranch->sum('total_sales');
        $stats[] = Stat::make('Total All Branches', '₱' . number_format($totalSales, 2))
            ->description('Combined Sales')
            ->color('primary')
            ->icon('heroicon-o-currency-dollar');

        return $stats;
    }
}
