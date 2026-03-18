<?php

namespace App\Filament\Tenant\Pages;

use App\Models\Inventory;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class OverstockReport extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-arrow-trending-up';
    protected static ?string $navigationLabel = 'Overstock Report';
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?int $navigationSort     = 11;
    protected static string $view             = 'filament.tenant.pages.overstock-report';

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
            ->where('overstock_threshold', '>', 0)
            ->with(['unitMeasure', 'defaultLocation', 'locationStocks.location'])
            ->get();

        $this->items = $inventories->map(function (Inventory $inventory) {
            $totalStock = $inventory->locationStocks->sum('current_stock');
            return [
                'id'                 => $inventory->id,
                'name'               => $inventory->name,
                'unit'               => $inventory->unitMeasure?->symbol ?? $inventory->unit_measure ?? '',
                'overstock_threshold' => $inventory->overstock_threshold,
                'total_stock'        => (float) $totalStock,
                'excess'             => max(0, $totalStock - $inventory->overstock_threshold),
                'default_location'   => $inventory->defaultLocation?->location ?? '—',
                'location_breakdown' => $inventory->locationStocks->map(fn ($s) => [
                    'location' => $s->location?->location ?? 'Unassigned',
                    'stock'    => (float) $s->current_stock,
                ])->values()->toArray(),
            ];
        })
        ->filter(fn ($item) => $item['total_stock'] >= $item['overstock_threshold'])
        ->sortByDesc('total_stock')
        ->values();
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Inventory::query()
            ->where('overstock_threshold', '>', 0)
            ->whereExists(function ($query) {
                $query->selectRaw(1)
                    ->from('inventory_location_stocks')
                    ->whereColumn('inventory_location_stocks.inventory_id', 'inventories.id')
                    ->groupBy('inventory_location_stocks.inventory_id')
                    ->havingRaw('SUM(current_stock) >= inventories.overstock_threshold');
            })
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
