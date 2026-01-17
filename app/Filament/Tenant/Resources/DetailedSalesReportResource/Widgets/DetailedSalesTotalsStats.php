<?php

namespace App\Filament\Tenant\Resources\DetailedSalesReportResource\Widgets;

use App\Filament\Tenant\Resources\DetailedSalesReportResource\Pages\ListDetailedSalesReports;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DetailedSalesTotalsStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListDetailedSalesReports::class;
    }

    protected function getStats(): array
    {
        $totals = (clone $this->getPageTableQuery())
            ->selectRaw('COALESCE(SUM(total_amount), 0) as gross_subtotal')
            ->selectRaw('COALESCE(SUM(item_discount), 0) as item_discount')
            ->selectRaw('COALESCE(SUM(less_tax), 0) as less_tax')
            ->selectRaw('COALESCE(SUM(service_charge), 0) as service_charge')
            ->selectRaw('COALESCE(SUM(total_due), 0) as total_due')
            ->first();

        $grossSales = (float) ($totals->gross_subtotal ?? 0);
        $discounts = (float) ($totals->item_discount ?? 0) + (float) ($totals->less_tax ?? 0);
        $serviceCharge = (float) ($totals->service_charge ?? 0);
        $netSales = (float) ($totals->total_due ?? 0);

        return [
            Stat::make('Gross Sales', '₱' . number_format($grossSales, 2))
                ->description('Based on current filters')
                ->color('primary')
                ->icon('heroicon-o-banknotes'),

            Stat::make('Discounts', '₱' . number_format($discounts, 2))
                ->description('Item discount + less tax')
                ->color('warning')
                ->icon('heroicon-o-tag'),

            Stat::make('Service Charge', '₱' . number_format($serviceCharge, 2))
                ->description('Added on top of net sales')
                ->color('info')
                ->icon('heroicon-o-receipt-refund'),

            Stat::make('Net Sales', '₱' . number_format($netSales, 2))
                ->description('Total due (excludes service charge)')
                ->color('success')
                ->icon('heroicon-o-currency-dollar'),
        ];
    }
}
