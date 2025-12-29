<?php

namespace App\Filament\Tenant\Resources\BestSellerReportResource\Widgets;

use App\Models\BestSellerReport;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TopProductsStats extends BaseWidget
{
    protected function getStats(): array
    {
        $topProducts = BestSellerReport::orderBy('qty_sold', 'desc')
            ->limit(5)
            ->get();

        $stats = [];

        foreach ($topProducts as $index => $product) {
            $stats[] = Stat::make('#' . ($index + 1) . ' ' . $product->product_name, number_format($product->qty_sold) . ' sold')
                ->description('₱' . number_format($product->net_sales, 2) . ' in sales')
                ->color('success')
                ->icon('heroicon-o-shopping-bag');
        }

        // Add total stats
        $totals = BestSellerReport::selectRaw('SUM(qty_sold) as total_qty, SUM(net_sales) as total_sales')->first();

        $stats[] = Stat::make('Total Quantity', number_format($totals->total_qty ?? 0))
            ->description('All Products')
            ->color('primary')
            ->icon('heroicon-o-cube');

        $stats[] = Stat::make('Total Sales', '₱' . number_format($totals->total_sales ?? 0, 2))
            ->description('All Products')
            ->color('primary')
            ->icon('heroicon-o-currency-dollar');

        return $stats;
    }
}
