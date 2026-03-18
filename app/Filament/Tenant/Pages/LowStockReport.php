<?php

namespace App\Filament\Tenant\Pages;

use App\Models\Inventory;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class LowStockReport extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationLabel = 'Low Stock Report';
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?int $navigationSort     = 10;
    protected static string $view             = 'filament.tenant.pages.low-stock-report';

    public Collection $items;
    public string $generatedAt;

    public function mount(): void
    {
        $this->generatedAt = now()->format('F d, Y h:i A');
        $this->loadItems();
    }

    public function loadItems(): void
    {
        $inventories = Inventory::query()
            ->where('low_stock_threshold', '>', 0)
            ->with(['unitMeasure', 'defaultLocation', 'locationStocks.location'])
            ->get();

        $this->items = $inventories->map(function (Inventory $inventory) {
            $totalStock = $inventory->locationStocks->sum('current_stock');
            return [
                'id'                 => $inventory->id,
                'name'               => $inventory->name,
                'unit'               => $inventory->unitMeasure?->symbol ?? $inventory->unit_measure ?? '',
                'low_threshold'      => $inventory->low_stock_threshold,
                'total_stock'        => (float) $totalStock,
                'is_critical'        => $totalStock <= 0,
                'shortage'           => max(0, $inventory->low_stock_threshold - $totalStock),
                'default_location'   => $inventory->defaultLocation?->location ?? '—',
                'location_breakdown' => $inventory->locationStocks->map(fn ($s) => [
                    'location' => $s->location?->location ?? 'Unassigned',
                    'stock'    => (float) $s->current_stock,
                ])->values()->toArray(),
            ];
        })
        ->filter(fn ($item) => $item['total_stock'] <= $item['low_threshold'])
        ->sortBy('total_stock')
        ->values();
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Inventory::query()
            ->where('low_stock_threshold', '>', 0)
            ->whereExists(function ($query) {
                $query->selectRaw(1)
                    ->from('inventory_location_stocks')
                    ->whereColumn('inventory_location_stocks.inventory_id', 'inventories.id')
                    ->groupBy('inventory_location_stocks.inventory_id')
                    ->havingRaw('SUM(current_stock) <= inventories.low_stock_threshold');
            })
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
