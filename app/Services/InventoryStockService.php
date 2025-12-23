<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryLocationStock;
use App\Models\InventoryPackaging;
use App\Models\InventoryStockMovement;
use App\Models\InventoryUnitConversion;
use App\Models\Location;
use App\Models\Order;
use App\Models\ProductInventory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class InventoryStockService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function adjust(array $data): InventoryStockMovement
    {
        return DB::transaction(fn () => $this->runAdjustment($data));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{0: InventoryStockMovement, 1: InventoryStockMovement}
     */
    public function transfer(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $inventory = Inventory::findOrFail($data['inventory_id']);
            $from      = Location::findOrFail($data['from_location_id']);
            $to        = Location::findOrFail($data['to_location_id']);

            if ($from->id === $to->id) {
                throw ValidationException::withMessages([
                    'to_location_id' => 'Destination must be different from the source location.',
                ]);
            }

            $baseQuantity = (float) ($data['base_quantity'] ?? 0);

            if ($baseQuantity <= 0) {
                throw ValidationException::withMessages([
                    'base_quantity' => 'Transfer quantity must be greater than zero.',
                ]);
            }

            $fromStock = InventoryLocationStock::where('inventory_id', $inventory->id)
                ->where('location_id', $from->id)
                ->first();

            $available = (float) ($fromStock?->current_stock ?? 0);

            if ($baseQuantity > $available) {
                throw ValidationException::withMessages([
                    'base_quantity' => sprintf(
                        'Only %s %s available in %s.',
                        $this->formatQuantity($available),
                        $inventory->unit_measure ?? 'units',
                        $from->location
                    ),
                ]);
            }

            $note = $data['notes'] ?? sprintf(
                'Transfer from %s to %s',
                $from->location,
                $to->location
            );

            $outMovement = $this->runAdjustment([
                'inventory_id'  => $inventory->id,
                'location_id'   => $from->id,
                'movement_type' => 'out',
                'quantity_mode' => 'base',
                'base_quantity' => $baseQuantity,
                'notes'         => $note,
            ]);

            $inMovement = $this->runAdjustment([
                'inventory_id'  => $inventory->id,
                'location_id'   => $to->id,
                'movement_type' => 'in',
                'quantity_mode' => 'base',
                'base_quantity' => $baseQuantity,
                'notes'         => $note,
            ]);

            return [$outMovement, $inMovement];
        });
    }

    public function deductOrderInventory(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order->loadMissing([
                'orderItems.product.inventoryRecipes.inventory.unitConversions',
                'orderItems.product.inventoryRecipes.inventory.packagings',
                'orderItems.product.inventoryRecipes.inventory.defaultLocation',
                'orderItems.product.inventoryRecipes.inventory.locationStocks',
            ]);

            foreach ($order->orderItems as $orderItem) {
                if ($orderItem->is_void) {
                    continue;
                }

                $product = $orderItem->product;

                if (! $product) {
                    continue;
                }

                if (! $product->track_inventory) {
                    continue;
                }

                if ($product->inventoryRecipes->isEmpty()) {
                    continue;
                }

                foreach ($product->inventoryRecipes as $recipe) {
                    $inventory = $recipe->inventory;

                    if (! $inventory) {
                        continue;
                    }

                    $locationId = $inventory->default_location;

                    if (! $locationId) {
                        $locationId = $inventory->locationStocks
                            ->sortByDesc(fn ($stock) => (float) ($stock->current_stock ?? 0))
                            ->first()?->location_id;
                    }

                    if (! $locationId) {
                        Log::warning('Inventory deduction skipped due to missing default location.', [
                            'inventory_id' => $inventory->id,
                            'order_id'     => $order->id,
                            'order_item_id'=> $orderItem->id,
                        ]);
                        continue;
                    }

                    $basePerUnit = $this->convertRecipeQuantityToBase($recipe);

                    if ($basePerUnit <= 0) {
                        continue;
                    }

                    $totalBase = $basePerUnit * (float) $orderItem->quantity;

                    if ($totalBase <= 0) {
                        continue;
                    }

                    try {
                        $this->runAdjustment([
                            'inventory_id'  => $inventory->id,
                            'location_id'   => $locationId,
                            'movement_type' => 'out',
                            'quantity_mode' => 'base',
                            'base_quantity' => $totalBase,
                            'notes'         => sprintf(
                                'Order #%s item %s',
                                $order->invoice_no ?? $order->id,
                                $orderItem->id
                            ),
                        ]);
                    } catch (ValidationException $exception) {
                        throw ValidationException::withMessages([
                            'inventory' => sprintf(
                                'Not enough %s in %s to fulfill %s (item #%s). Move stock to the default location then try again.',
                                $inventory->name,
                                $inventory->defaultLocation?->location ?? 'the default location',
                                $product->name,
                                $orderItem->id
                            ),
                        ]);
                    }
                }
            }
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function runAdjustment(array $data): InventoryStockMovement
    {
        $inventory = Inventory::findOrFail($data['inventory_id']);
        $location  = Location::findOrFail($data['location_id']);
        $movementType = $data['movement_type'];
        $quantityMode = $data['quantity_mode'] ?? 'base';

        if ($movementType === 'out') {
            $quantityMode = 'base';
        }

        [$baseQuantity, $unitType, $unitReferenceId] = $this->resolveQuantity(
            $movementType,
            $quantityMode,
            $data,
            $inventory
        );

        if ($baseQuantity <= 0) {
            throw ValidationException::withMessages([
                'base_quantity' => 'Quantity must be greater than zero.',
            ]);
        }

        $stock = InventoryLocationStock::firstOrCreate(
            [
                'inventory_id' => $inventory->id,
                'location_id'  => $location->id,
            ],
            [
                'current_stock' => 0,
            ]
        );

        $adjustment = $movementType === 'out'
            ? $baseQuantity * -1
            : $baseQuantity;

        $updatedStock = (float) $stock->current_stock + (float) $adjustment;

        if ($updatedStock < 0) {
            throw ValidationException::withMessages([
                'base_quantity' => 'Insufficient stock at the selected location.',
            ]);
        }

        $stock->current_stock = $updatedStock;
        $stock->save();

        return InventoryStockMovement::create([
            'inventory_id'    => $inventory->id,
            'location_id'     => $location->id,
            'movement_type'   => $movementType,
            'quantity'        => abs((float) $baseQuantity),
            'unit_type'       => $unitType,
            'unit_reference_id' => $unitReferenceId,
            'resulting_stock' => $updatedStock,
            'user_id'         => Auth::id(),
            'notes'           => $data['notes'] ?? null,
        ]);
    }

    /**
     * @return array{0: float, 1: string, 2: int|null}
     */
    private function resolveQuantity(string $movementType, string $quantityMode, array $data, Inventory $inventory): array
    {
        if ($quantityMode === 'packaging') {
            if ($movementType !== 'in') {
                throw ValidationException::withMessages([
                    'quantity_mode' => 'Packaging can only be used when adding stock.',
                ]);
            }

            $packagingId = $data['packaging_id'] ?? null;
            $packagesCount = (float) ($data['packages_count'] ?? 0);

            if (!$packagingId || $packagesCount <= 0) {
                throw ValidationException::withMessages([
                    'packages_count' => 'Please enter a valid number of packages.',
                ]);
            }

            $packaging = InventoryPackaging::query()
                ->where('inventory_id', $inventory->id)
                ->findOrFail($packagingId);

            $perPackage = (float) $packaging->quantity;
            $baseQuantity = $packagesCount * $perPackage;

            return [$baseQuantity, 'packaging', (int) $packaging->id];
        }

        $baseQuantity = (float) ($data['base_quantity'] ?? 0);

        return [$baseQuantity, 'base', null];
    }

    private function convertRecipeQuantityToBase(ProductInventory $recipe): float
    {
        $quantity = (float) ($recipe->quantity ?? 0);

        return match ($recipe->unit_type) {
            'conversion' => $this->convertUsingConversion($recipe, $quantity),
            'packaging'  => $this->convertUsingPackaging($recipe, $quantity),
            default      => $quantity,
        };
    }

    private function formatQuantity(float $value): string
    {
        $formatted = number_format($value, 4, '.', '');
        $trimmed   = rtrim(rtrim($formatted, '0'), '.');

        return $trimmed === '' ? '0' : $trimmed;
    }

    private function convertUsingConversion(ProductInventory $recipe, float $quantity): float
    {
        if (! $recipe->unit_reference_id) {
            return $quantity;
        }

        $inventory  = $recipe->inventory;
        $conversion = $inventory && $inventory->relationLoaded('unitConversions')
            ? $inventory->unitConversions->firstWhere('id', $recipe->unit_reference_id)
            : InventoryUnitConversion::find($recipe->unit_reference_id);

        if (! $conversion) {
            return $quantity;
        }

        $factor = (float) $conversion->conversion_factor;

        if ($factor <= 0) {
            return $quantity;
        }

        return $quantity / $factor;
    }

    private function convertUsingPackaging(ProductInventory $recipe, float $quantity): float
    {
        if (! $recipe->unit_reference_id) {
            return $quantity;
        }

        $inventory = $recipe->inventory;
        $packaging = $inventory && $inventory->relationLoaded('packagings')
            ? $inventory->packagings->firstWhere('id', $recipe->unit_reference_id)
            : InventoryPackaging::find($recipe->unit_reference_id);

        if (! $packaging) {
            return $quantity;
        }

        $perPackage = (float) $packaging->quantity;

        if ($perPackage <= 0) {
            return $quantity;
        }

        return $quantity * $perPackage;
    }
}
