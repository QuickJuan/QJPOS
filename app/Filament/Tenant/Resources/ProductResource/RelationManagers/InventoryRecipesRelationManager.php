<?php
namespace App\Filament\Tenant\Resources\ProductResource\RelationManagers;

use App\Models\Inventory;
use App\Models\ProductInventory;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

class InventoryRecipesRelationManager extends RelationManager
{
    protected static string $relationship = 'inventoryRecipes';

    protected static ?string $title = 'Inventory Associations';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        if ($ownerRecord->product_type === 'simple') {
            return (bool) $ownerRecord->track_inventory;
        }

        return $ownerRecord->product_type === 'composite';
    }

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
                        $set('unit_type', 'base');
                        $set('unit_reference_id', null);
                    })
                    ->unique(
                        ignoreRecord: true,
                        table: ProductInventory::class,
                        column: 'inventory_id',
                        modifyRuleUsing: function (Unique $rule) {
                            return $rule->where('product_id', $this->getOwnerRecord()->id);
                        }
                    ),

                TextInput::make('quantity')
                    ->label('Quantity Used')
                    ->numeric()
                    ->required()
                    ->minValue(0.01)
                    ->step(0.01)
                    ->default(1),

                Toggle::make('use_custom_unit')
                    ->label('Use Conversion / Packaging')
                    ->helperText('Enable to deduct via a conversion unit or packaging defined on the inventory.')
                    ->dehydrated(false)
                    ->reactive()
                    ->hidden(fn (Get $get) => blank($get('inventory_id')))
                    ->afterStateHydrated(function (Toggle $component, ?bool $state, ?ProductInventory $record) {
                        if ($record) {
                            $component->state($record->unit_type !== 'base');
                        }
                    })
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
                    ->options(fn (Get $get) => $this->getUnitOptions($get('inventory_id')))
                    ->afterStateHydrated(function (Select $component, $state, ?ProductInventory $record) {
                        if (! $record) {
                            return;
                        }

                        if ($record->unit_type === 'base' || ! $record->unit_reference_id) {
                            $component->state('base:0');
                            return;
                        }

                        $component->state(sprintf('%s:%s', $record->unit_type, $record->unit_reference_id));
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
            ->columns([
                TextColumn::make('inventory.name')
                    ->label('Inventory')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('quantity')
                    ->label('Quantity')
                    ->formatStateUsing(fn ($state) => $this->formatDecimal($state))
                    ->sortable(),

                TextColumn::make('unit_measure')
                    ->label('Unit'),

                TextColumn::make('unit_type')
                    ->label('Type')
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'conversion' => 'Conversion',
                            'packaging'  => 'Packaging',
                            default      => 'Base',
                        };
                    })
                    ->badge(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Inventory'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('inventory.name');
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
