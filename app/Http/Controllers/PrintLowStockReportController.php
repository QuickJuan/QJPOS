<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Settings\GeneralSettings;

class PrintLowStockReportController extends Controller
{
    public function __invoke(GeneralSettings $settings)
    {
        $inventories = Inventory::query()
            ->where('low_stock_threshold', '>', 0)
            ->with(['unitMeasure', 'defaultLocation', 'locationStocks.location'])
            ->get();

        $items = $inventories->map(function (Inventory $inventory) {
            $totalStock = $inventory->locationStocks->sum('current_stock');
            return [
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

        return view('reports.low-stock-report-print', [
            'items'         => $items,
            'generatedAt'   => now()->format('F d, Y h:i A'),
            'companyName'   => $settings->company_name ?? 'QuickJuan POS',
            'companyAddress' => $settings->company_address,
        ]);
    }
}
