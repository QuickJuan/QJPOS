<?php

namespace App\Filament\Tenant\Resources\DailySummaryReportResource\Widgets;

use App\Models\DailySummaryReport;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesByCategoryStats extends BaseWidget
{
    protected function getStats(): array
    {
        $salesByCategory = DailySummaryReport::select('category_id', 'category_name')
            ->selectRaw('SUM(sub_total) as total_sales')
            ->groupBy('category_id', 'category_name')
            ->orderBy('total_sales', 'desc')
            ->get();

        $stats = [];

        foreach ($salesByCategory as $category) {
            $stats[] = Stat::make($category->category_name ?? 'Unknown', '₱' . number_format($category->total_sales, 2))
                ->description('Category Sales')
                ->color('success')
                ->icon('heroicon-o-tag');
        }

        $totalSales = $salesByCategory->sum('total_sales');
        $stats[] = Stat::make('Total Sales', '₱' . number_format($totalSales, 2))
            ->description('All Categories')
            ->color('primary')
            ->icon('heroicon-o-currency-dollar');

        return $stats;
    }
}
