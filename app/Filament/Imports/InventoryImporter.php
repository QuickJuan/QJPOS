<?php
namespace App\Filament\Imports;

use App\Models\Inventory;
use App\Models\Location;
use App\Models\UnitMeasure;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class InventoryImporter extends Importer
{
    protected static ?string $model = Inventory::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Name')
                ->fillRecordUsing(fn() => null),

            ImportColumn::make('unit_measure')
                ->label('Unit Measure')
                ->fillRecordUsing(fn() => null),

            ImportColumn::make('cost')
                ->label('Cost')
                ->fillRecordUsing(fn() => null),

            ImportColumn::make('default_location')
                ->label('Default Location')
                ->fillRecordUsing(fn() => null),
        ];
    }

    public function resolveRecord(): ?Inventory
    {
        $defaultLocation = ! empty($this->data['default_location'])
            ? Location::firstOrCreate(
            ['location' => $this->data['default_location']],
            ['location' => $this->data['default_location']]
        )
            : null;

        $unitMeasure = ! empty($this->data['unit_measure'])
            ? UnitMeasure::firstOrCreate(
                ['name' => $this->data['unit_measure']],
                ['symbol' => strtoupper(substr($this->data['unit_measure'], 0, 5))]
            )
            : null;

        return Inventory::firstOrCreate(
            [
                'name'             => $this->data['name'],
                'default_location' => $defaultLocation?->id,
            ],
            [
                'default_location' => $defaultLocation?->id ?? null,
                'name'             => $this->data['name'],
                'unit_measure'     => $unitMeasure?->name,
                'unit_measure_id'  => $unitMeasure?->id,
                'cost'             => $this->data['cost'],
            ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your inventory import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
