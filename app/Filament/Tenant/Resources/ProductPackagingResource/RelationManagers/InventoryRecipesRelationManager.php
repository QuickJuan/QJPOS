<?php

namespace App\Filament\Tenant\Resources\ProductPackagingResource\RelationManagers;

use App\Models\Inventory;
use App\Models\InventoryPackaging;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class InventoryRecipesRelationManager extends RelationManager
{
    protected static string $relationship = 'inventoryRecipes';
    protected static ?string $title = 'Inventory';
    protected static ?string $modelLabel = 'Inventory Item';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('inventory_id')
                    ->label('Inventory Item')
                    ->options(fn () => Inventory::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->required()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if (! $state) {
                            $set('unit_measure', null);
                            $set('unit_option', null);
                            $set('unit_type', 'base');
                            $set('unit_reference_id', null);
                            return;
                        }

                        $inventory = Inventory::find($state);
                        $set('unit_measure', $inventory?->unit_measure);
                        $set('unit_option', null);
                    }),

                TextInput::make('quantity')
                    ->label('Quantity Used')
                    ->numeric()
                    ->minValue(0.01)
                    ->step(0.01)
                    ->default(1)
                    ->required(),

                Toggle::make('use_custom_unit')
                    ->label('Use Conversion / Packaging')
                    ->helperText('Enable to use a unit conversion or packaging for this ingredient.')
                    ->hidden(fn (Get $get) => blank($get('inventory_id')))
                    ->dehydrated(false)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if (! $state) {
                            $inventoryId = $get('inventory_id');
                            $inventory   = $inventoryId ? Inventory::find($inventoryId) : null;
                            $set('unit_option', null);
                            $set('unit_type', 'base');
                            $set('unit_reference_id', null);
                            $set('unit_measure', $inventory?->unit_measure);
                        }
                    }),

                Select::make('unit_option')
                    ->label('Select Conversion / Packaging')
                    ->hidden(fn (Get $get) => blank($get('inventory_id')) || ! $get('use_custom_unit'))
                    ->required(fn (Get $get) => (bool) $get('use_custom_unit'))
                    ->dehydrated(false)
                    ->reactive()
                    ->placeholder('Choose a conversion or packaging')
                    ->options(function (Get $get) {
                        return $this->getUnitOptions($get('inventory_id'));
                    })
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $inventoryId = $get('inventory_id');

                        if (! $inventoryId) {
                            $set('unit_type', 'base');
                            $set('unit_reference_id', null);
                            $set('unit_measure', null);
                            return;
                        }

                        $inventory = Inventory::with(['unitConversions.unitMeasure', 'packagings'])->find($inventoryId);

                        if (! $inventory) {
                            $set('unit_type', 'base');
                            $set('unit_reference_id', null);
                            $set('unit_measure', null);
                            return;
                        }

                        if (! $state || $state === 'base:0') {
                            $set('unit_type', 'base');
                            $set('unit_reference_id', null);
                            $set('unit_measure', $inventory->unit_measure);
                            return;
                        }

                        [$type, $id] = array_pad(explode(':', $state), 2, null);
                        $id = $id ? (int) $id : null;

                        if ($type === 'conversion' && $id) {
                            $conversion = $inventory->unitConversions->firstWhere('id', $id);
                            $set('unit_type', 'conversion');
                            $set('unit_reference_id', $conversion?->id);
                            $set('unit_measure', $conversion?->unitMeasure?->name ?? $inventory->unit_measure);
                            return;
                        }

                        if ($type === 'packaging' && $id) {
                            $packaging = $inventory->packagings->firstWhere('id', $id);
                            $set('unit_type', 'packaging');
                            $set('unit_reference_id', $packaging?->id);
                            $set('unit_measure', $packaging?->name ?? $inventory->unit_measure);
                            return;
                        }

                        $set('unit_type', 'base');
                        $set('unit_reference_id', null);
                        $set('unit_measure', $inventory->unit_measure);
                    }),

                TextInput::make('unit_measure')
                    ->label('Unit of Measure')
                    ->maxLength(50)
                    ->disabled()
                    ->placeholder('Auto-filled from the selected unit')
                    ->helperText('Reflects the base unit, conversion, or packaging that will be deducted.')
                    ->dehydrated(true),

                Hidden::make('unit_type')
                    ->default('base'),

                Hidden::make('unit_reference_id')
                    ->default(null),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('inventory.name')
            ->columns([
                TextColumn::make('inventory.name')
                    ->label('Inventory Item')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('quantity')
                    ->label('Quantity')
                    ->formatStateUsing(fn ($state) => $this->formatDecimal($state)),

                TextColumn::make('unit_measure')
                    ->label('Unit')
                    ->formatStateUsing(function ($record) {
                        $typeLabel = match ($record->unit_type) {
                            'conversion' => ' (Conversion)',
                            'packaging'  => ' (Packaging)',
                            default       => '',
                        };
                        return ($record->unit_measure ?? '') . $typeLabel;
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        if ($data['unit_type'] ?? 'base' !== 'base') {
                            $data['use_custom_unit'] = true;
                            $data['unit_option'] = ($data['unit_type'] ?? 'base') . ':' . ($data['unit_reference_id'] ?? '0');
                        }
                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected function getUnitOptions(?int $inventoryId): array
    {
        if (! $inventoryId) {
            return [];
        }

        $inventory = Inventory::with(['unitConversions.unitMeasure', 'packagings'])->find($inventoryId);

        if (! $inventory) {
            return [];
        }

        $options = [];

        if ($inventory->unit_measure) {
            $options['base:0'] = 'Base Unit - ' . $inventory->unit_measure;
        }

        foreach ($inventory->unitConversions as $conversion) {
            $unitName = $conversion->unitMeasure?->name ?? 'Conversion';
            $factor = $conversion->conversion_factor ?? 0;
            $formattedFactor = $this->formatDecimal($factor);
            $options['conversion:' . $conversion->id] = sprintf(
                'Conversion - %s (1 %s = %s %s)',
                $unitName,
                $unitName,
                $formattedFactor,
                $inventory->unit_measure ?? 'base units'
            );
        }

        foreach ($inventory->packagings as $packaging) {
            $qty = $packaging->quantity ?? 0;
            $formattedQty = $this->formatDecimal($qty);
            $options['packaging:' . $packaging->id] = sprintf(
                'Packaging - %s (%s %s per package)',
                $packaging->name,
                $formattedQty,
                $inventory->unit_measure ?? 'base units'
            );
        }

        return $options;
    }

    protected function formatDecimal(null|int|float|string $value): string
    {
        $number = (float) $value;
        $formatted = number_format($number, 4, '.', '');

        return Str::of($formatted)
            ->rtrim('0')
            ->rtrim('.')
            ->value() ?: '0';
    }
}
