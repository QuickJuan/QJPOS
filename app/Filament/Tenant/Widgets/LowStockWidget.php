<?php

namespace App\Filament\Tenant\Widgets;

use App\Models\Inventory;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LowStockWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $lowStockItems  = $this->getLowStockCount();
        $overstockItems = $this->getOverstockCount();
        $criticalItems  = $this->getCriticalStockCount();

        return [
            Stat::make('Low Stock Items', $lowStockItems)
                ->description($lowStockItems > 0 ? 'Items at or below threshold — restock needed' : 'All items are adequately stocked')
                ->descriptionIcon($lowStockItems > 0 ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle')
                ->color($lowStockItems > 0 ? 'danger' : 'success')
                ->url(\App\Filament\Tenant\Pages\LowStockReport::getUrl()),

            Stat::make('Overstock Items', $overstockItems)
                ->description($overstockItems > 0 ? 'Items exceeding maximum threshold' : 'No overstock detected')
                ->descriptionIcon($overstockItems > 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-check-circle')
                ->color($overstockItems > 0 ? 'warning' : 'success')
                ->url(\App\Filament\Tenant\Pages\OverstockReport::getUrl()),

            Stat::make('Critical (Zero Stock)', $criticalItems)
                ->description($criticalItems > 0 ? 'Items with no stock at any location' : 'All items have stock')
                ->descriptionIcon($criticalItems > 0 ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                ->color($criticalItems > 0 ? 'danger' : 'success'),
        ];
    }

    private function getLowStockCount(): int
    {
        return Inventory::query()
            ->where('low_stock_threshold', '>', 0)
            ->whereExists(function ($query) {
                $query->selectRaw(1)
                    ->from('inventory_location_stocks')
                    ->whereColumn('inventory_location_stocks.inventory_id', 'inventories.id')
                    ->groupBy('inventory_location_stocks.inventory_id')
                    ->havingRaw('SUM(current_stock) <= inventories.low_stock_threshold');
            })
            ->count();
    }

    private function getOverstockCount(): int
    {
        return Inventory::query()
            ->where('overstock_threshold', '>', 0)
            ->whereExists(function ($query) {
                $query->selectRaw(1)
                    ->from('inventory_location_stocks')
                    ->whereColumn('inventory_location_stocks.inventory_id', 'inventories.id')
                    ->groupBy('inventory_location_stocks.inventory_id')
                    ->havingRaw('SUM(current_stock) >= inventories.overstock_threshold');
            })
            ->count();
    }

    private function getCriticalStockCount(): int
    {
        return Inventory::query()
            ->where('low_stock_threshold', '>', 0)
            ->whereDoesntHave('locationStocks', function ($query) {
                $query->where('current_stock', '>', 0);
            })
            ->count();
    }
}

