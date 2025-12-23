<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductInventory;
use App\Models\UnitMeasure;
use Illuminate\Support\Str;

class ProductInventorySyncService
{
    public function ensureInventoryLink(Product $product): void
    {
        if ($product->product_type !== 'simple' || ! $product->track_inventory) {
            return;
        }

        $alreadyLinked = ProductInventory::where('product_id', $product->id)->exists();

        if ($alreadyLinked) {
            return;
        }

        $unitName = trim((string) ($product->unit_measure ?? ''));

        if ($unitName === '') {
            $unitName = 'Unit';
        }

        $unitMeasure = $this->resolveUnitMeasure($unitName);

        $inventory = Inventory::create([
            'name'             => $product->name,
            'unit_measure'     => $unitName,
            'unit_measure_id'  => $unitMeasure?->id,
            'cost'             => $product->price ?? 0,
            'default_location' => null,
        ]);

        ProductInventory::create([
            'product_id'        => $product->id,
            'inventory_id'      => $inventory->id,
            'quantity'          => 1,
            'unit_measure'      => $unitName,
            'unit_type'         => 'base',
            'unit_reference_id' => null,
        ]);
    }

    private function resolveUnitMeasure(string $unitName): ?UnitMeasure
    {
        $existing = UnitMeasure::where('name', $unitName)->first();

        if ($existing) {
            return $existing;
        }

        return UnitMeasure::create([
            'name'   => $unitName,
            'symbol' => Str::upper(Str::substr($unitName, 0, 5)),
        ]);
    }
}
