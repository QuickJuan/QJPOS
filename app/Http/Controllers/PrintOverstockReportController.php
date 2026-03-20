<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Settings\GeneralSettings;

class PrintOverstockReportController extends Controller
{
    public function __invoke(GeneralSettings $settings)
    {
        $inventories = Inventory::query()
            ->where('overstock_threshold', '>', 0)
            ->with(['unitMeasure', 'defaultLocation', 'locationStocks.location'])
            ->get();

        $items = $inventories->map(function (Inventory $inventory) {
            $totalStock = $inventory->locationStocks->sum('current_stock');
            return [
                'name'                => $inventory->name,
                'unit'                => $inventory->unitMeasure?->symbol ?? $inventory->unit_measure ?? '',
                'overstock_threshold' => $inventory->overstock_threshold,
                'total_stock'         => (float) $totalStock,
                'excess'              => max(0, $totalStock - $inventory->overstock_threshold),
                'default_location'    => $inventory->defaultLocation?->location ?? '—',
                'location_breakdown'  => $inventory->locationStocks->map(fn ($s) => [
                    'location' => $s->location?->location ?? 'Unassigned',
                    'stock'    => (float) $s->current_stock,
                ])->values()->toArray(),
            ];
        })
        ->filter(fn ($item) => $item['total_stock'] >= $item['overstock_threshold'])
        ->sortByDesc('total_stock')
        ->values();

        return view('reports.overstock-report-print', [
            'items'          => $items,
            'generatedAt'    => now()->format('F d, Y h:i A'),
            'companyName'    => $settings->company_name ?? 'QuickJuan POS',
            'companyAddress' => $settings->company_address,
        ]);
    }
}
