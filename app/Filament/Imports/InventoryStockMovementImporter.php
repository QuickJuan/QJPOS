<?php
namespace App\Filament\Imports;

use App\Models\Inventory;
use App\Models\InventoryStockMovement;
use App\Models\Location;
use App\Services\InventoryStockService;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Validation\ValidationException;

class InventoryStockMovementImporter extends Importer
{
    protected static ?string $model = InventoryStockMovement::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('inventory')
                ->label('Inventory (ID or Name)')
                ->requiredMapping()
                ->rules(['required'])
                ->fillRecordUsing(fn () => null),

            ImportColumn::make('location')
                ->label('Location (ID or Name)')
                ->requiredMapping()
                ->rules(['required'])
                ->fillRecordUsing(fn () => null),

            ImportColumn::make('movement_type')
                ->label('Movement Type (in / out)')
                ->requiredMapping()
                ->rules(['required'])
                ->fillRecordUsing(fn () => null),

            ImportColumn::make('quantity')
                ->label('Quantity (Base Units)')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric', 'gt:0'])
                ->fillRecordUsing(fn () => null),

            ImportColumn::make('notes')
                ->label('Notes')
                ->fillRecordUsing(fn () => null),
        ];
    }

    public function resolveRecord(): ?InventoryStockMovement
    {
        return new InventoryStockMovement();
    }

    public function saveRecord(): void
    {
        $inventoryIdentifier = trim((string) ($this->data['inventory'] ?? ''));
        $locationIdentifier  = trim((string) ($this->data['location'] ?? ''));
        $movementRaw         = trim(strtolower((string) ($this->data['movement_type'] ?? '')));
        $quantity            = (float) ($this->data['quantity'] ?? 0);

        if ($inventoryIdentifier === '') {
            throw ValidationException::withMessages([
                'inventory' => 'Inventory value is required.',
            ]);
        }

        if ($locationIdentifier === '') {
            throw ValidationException::withMessages([
                'location' => 'Location value is required.',
            ]);
        }

        $inventory = $this->resolveInventory($inventoryIdentifier);

        if (! $inventory) {
            throw ValidationException::withMessages([
                'inventory' => "Inventory '{$inventoryIdentifier}' was not found.",
            ]);
        }

        $location = $this->resolveLocation($locationIdentifier);

        if (! $location) {
            throw ValidationException::withMessages([
                'location' => "Location '{$locationIdentifier}' was not found.",
            ]);
        }

        $movementType = $this->normalizeMovementType($movementRaw);

        if (! $movementType) {
            throw ValidationException::withMessages([
                'movement_type' => 'Movement type must be IN or OUT.',
            ]);
        }

        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Quantity must be greater than zero.',
            ]);
        }

        $movement = app(InventoryStockService::class)->adjust([
            'inventory_id'  => $inventory->id,
            'location_id'   => $location->id,
            'movement_type' => $movementType,
            'quantity_mode' => 'base',
            'base_quantity' => $quantity,
            'notes'         => $this->data['notes'] ?? null,
        ]);

        $this->record = $movement;
    }

    protected function resolveInventory(string $identifier): ?Inventory
    {
        if (is_numeric($identifier)) {
            $inventory = Inventory::find((int) $identifier);

            if ($inventory) {
                return $inventory;
            }
        }

        return Inventory::where('name', $identifier)->first();
    }

    protected function resolveLocation(string $identifier): ?Location
    {
        if (is_numeric($identifier)) {
            $location = Location::find((int) $identifier);

            if ($location) {
                return $location;
            }
        }

        return Location::where('location', $identifier)->first();
    }

    protected function normalizeMovementType(string $value): ?string
    {
        return match ($value) {
            'in', 'stock in', 'add', 'addition', 'receive', 'received' => 'in',
            'out', 'stock out', 'deduct', 'deduction', 'issue', 'issued' => 'out',
            default => null,
        };
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your stock movement import completed with ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' processed.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
