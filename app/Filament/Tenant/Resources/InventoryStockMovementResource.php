<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\InventoryStockMovementResource\Pages;
use App\Models\Inventory;
use App\Models\InventoryPackaging;
use App\Models\InventoryStockMovement;
use App\Services\InventoryStockService;
use Filament\Forms;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InventoryStockMovementResource extends Resource
{
    protected static ?string $model = InventoryStockMovement::class;

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected static ?string $navigationLabel = 'Stock Movements';

    protected static ?string $modelLabel = 'Stock Movement';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('inventory_id')
                    ->label('Inventory Item')
                    ->relationship('inventory', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if (! $state) {
                            $set('packaging_id', null);
                        }
                    }),

                Select::make('location_id')
                    ->label('Location')
                    ->relationship('location', 'location')
                    ->required()
                    ->searchable()
                    ->preload(),

                Radio::make('movement_type')
                    ->label('Movement Type')
                    ->options([
                        'in'  => 'Add Stock',
                        'out' => 'Deduct Stock',
                    ])
                    ->default('in')
                    ->live(),

                Radio::make('quantity_mode')
                    ->label('Quantity Input')
                    ->options(function (Get $get) {
                        if ($get('movement_type') === 'out') {
                            return ['base' => 'Base Unit'];
                        }

                        return [
                            'base'      => 'Base Unit',
                            'packaging' => 'Packaging (no conversions)',
                        ];
                    })
                    ->default('base')
                    ->live()
                    ->helperText('Inventory balances are stored in base units. Packaging automatically converts to base.'),

                TextInput::make('base_quantity')
                    ->label('Base Quantity')
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0.01)
                    ->required(fn (Get $get) => $get('movement_type') === 'out' || $get('quantity_mode') === 'base')
                    ->visible(fn (Get $get) => $get('movement_type') === 'out' || $get('quantity_mode') === 'base')
                    ->helperText('Enter the amount in the inventory\'s base unit.')
                    ->default(1),

                Select::make('packaging_id')
                    ->label('Packaging')
                    ->options(function (Get $get) {
                        $inventoryId = $get('inventory_id');

                        if (! $inventoryId) {
                            return [];
                        }

                        return InventoryPackaging::query()
                            ->where('inventory_id', $inventoryId)
                            ->get()
                            ->mapWithKeys(fn ($packaging) => [
                                $packaging->id => sprintf(
                                    '%s (%s base units)',
                                    $packaging->name,
                                    number_format((float) $packaging->quantity, 2)
                                ),
                            ]);
                    })
                    ->searchable()
                    ->preload()
                    ->visible(fn (Get $get) => $get('movement_type') === 'in' && $get('quantity_mode') === 'packaging')
                    ->required(fn (Get $get) => $get('movement_type') === 'in' && $get('quantity_mode') === 'packaging')
                    ->helperText('Select the packaging that will be used to add stock.'),

                TextInput::make('packages_count')
                    ->label('Number of Packages')
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0.01)
                    ->visible(fn (Get $get) => $get('movement_type') === 'in' && $get('quantity_mode') === 'packaging')
                    ->required(fn (Get $get) => $get('movement_type') === 'in' && $get('quantity_mode') === 'packaging')
                    ->default(1)
                    ->helperText('Packages will be converted to base units automatically.'),

                Textarea::make('notes')
                    ->label('Notes')
                    ->rows(3)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('inventory.name')
                    ->label('Inventory')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('location.location')
                    ->label('Location')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('movement_type')
                    ->label('Type')
                    ->badge()
                    ->colors([
                        'success' => 'in',
                        'danger'  => 'out',
                    ])
                    ->formatStateUsing(fn (string $state) => $state === 'in' ? 'Stock In' : 'Stock Out'),

                TextColumn::make('quantity')
                    ->label('Quantity (Base)')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2))
                    ->sortable(),

                TextColumn::make('packaging.name')
                    ->label('Packaging')
                    ->toggleable()
                    ->placeholder('-'),

                TextColumn::make('resulting_stock')
                    ->label('Balance')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2))
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Recorded By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
            ])
            ->headerActions([
                Action::make('transferStock')
                    ->label('Transfer Stock')
                    ->icon('heroicon-m-arrows-right-left')
                    ->modalHeading('Move Stock Between Locations')
                    ->modalWidth('lg')
                    ->form([
                        Select::make('inventory_id')
                            ->label('Inventory Item')
                            ->options(fn () => Inventory::orderBy('name')->pluck('name', 'id'))
                            ->reactive()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (! $state) {
                                    $set('from_location_id', null);
                                    $set('to_location_id', null);
                                }
                            }),

                        Select::make('from_location_id')
                            ->label('From Location')
                            ->options(fn (Get $get) => self::getLocationOptions($get('inventory_id')))
                            ->searchable()
                            ->required()
                            ->disabled(fn (Get $get) => blank($get('inventory_id')))
                            ->helperText('Only locations linked to this inventory are listed.'),

                        Select::make('to_location_id')
                            ->label('To Location')
                            ->options(fn (Get $get) => self::getLocationOptions($get('inventory_id')))
                            ->searchable()
                            ->required()
                            ->disabled(fn (Get $get) => blank($get('inventory_id')))
                            ->rule('different:from_location_id')
                            ->helperText('Destination must be different from the source location.'),

                        TextInput::make('base_quantity')
                            ->label('Quantity (Base Units)')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0.01)
                            ->required(),

                        Textarea::make('notes')
                            ->label('Notes (optional)')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->action(function (array $data) {
                        app(InventoryStockService::class)->transfer([
                            'inventory_id'      => $data['inventory_id'],
                            'from_location_id'  => $data['from_location_id'],
                            'to_location_id'    => $data['to_location_id'],
                            'base_quantity'     => $data['base_quantity'],
                            'notes'             => $data['notes'] ?? null,
                        ]);

                        Notification::make()
                            ->title('Stock transferred')
                            ->body('Inventory was moved between the selected locations.')
                            ->success()
                            ->send();
                    })
                    ->modalSubmitActionLabel('Transfer'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventoryStockMovements::route('/'),
            'create' => Pages\CreateInventoryStockMovement::route('/create'),
            'view' => Pages\ViewInventoryStockMovement::route('/{record}'),
        ];
    }

    protected static function getLocationOptions(?int $inventoryId): array
    {
        if (! $inventoryId) {
            return [];
        }

        $inventory = Inventory::with(['locationStocks.location', 'defaultLocation'])->find($inventoryId);

        if (! $inventory) {
            return [];
        }

        $options = [];

        foreach ($inventory->locationStocks as $stock) {
            if ($stock->location) {
                $options[$stock->location->id] = sprintf(
                    '%s — %s %s',
                    $stock->location->location,
                    InventoryResource::formatStock($stock->current_stock ?? 0),
                    $inventory->unit_measure ?? 'units'
                );
            }
        }

        if ($inventory->defaultLocation && ! isset($options[$inventory->defaultLocation->id])) {
            $options[$inventory->defaultLocation->id] = sprintf(
                '%s (Default) — %s %s',
                $inventory->defaultLocation->location,
                InventoryResource::formatStock(
                    $inventory->locationStocks
                        ->firstWhere('location_id', $inventory->defaultLocation->id)?->current_stock ?? 0
                ),
                $inventory->unit_measure ?? 'units'
            );
        }

        asort($options, SORT_NATURAL | SORT_FLAG_CASE);

        return $options;
    }
}
