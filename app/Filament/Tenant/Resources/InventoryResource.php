<?php
namespace App\Filament\Tenant\Resources;

use App\Filament\Imports\InventoryImporter;
use App\Filament\Tenant\Resources\InventoryResource\Pages;
use App\Models\Inventory;
use App\Models\InventoryLocationStock;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InventoryResource extends Resource
{
    protected static ?string $model           = Inventory::class;
    protected static ?string $navigationGroup = "Inventory";
    protected static ?string $navigationIcon  = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required(),

                Select::make('unit_measure_id')
                    ->label('Unit Measure')
                    ->relationship('unitMeasure', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(100),

                        TextInput::make('symbol')
                            ->label('Symbol / Abbrev')
                            ->maxLength(25),
                    ])
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if (! $state) {
                            return;
                        }

                        $unit = \App\Models\UnitMeasure::find($state);
                        $set('unit_measure', $unit?->name);
                    }),

                Hidden::make('unit_measure')
                    ->default(fn (?Inventory $record) => $record?->unit_measure)
                    ->dehydrated(),

                TextInput::make('cost')
                    ->numeric()
                    ->prefix('₱')
                    ->rules(['numeric'])
                    ->required(),

                Select::make('default_location')
                    ->relationship('defaultLocation', 'location')
                    ->preload()
                    ->searchable(),

                Repeater::make('unitConversions')
                    ->label('Unit Conversions (Smaller Units)')
                    ->relationship('unitConversions')
                    ->hidden(fn (Get $get) => blank($get('unit_measure_id')))
                    ->schema([
                        Select::make('unit_measure_id')
                            ->label('Conversion Unit (smaller)')
                            ->relationship('unitMeasure', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('Pick a smaller unit that breaks down the selected base unit (e.g., liter → milliliter).'),

                        TextInput::make('conversion_factor')
                            ->label('Base Units per Conversion Unit')
                            ->numeric()
                            ->minValue(0)
                            ->required()
                        ->rule('decimal:0,2')
                        ->step(0.01)
                        ->formatStateUsing(fn ($state) => $state === null
                            ? null
                            : ((float) $state == (int) $state
                                ? (string) (int) $state
                                : number_format((float) $state, 2, '.', '')))
                                ->helperText('Enter how many of the smaller unit make up 1 base unit (e.g., 1 liter = 1,000 milliliters).'),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->collapsible()
                    ->addActionLabel('Add Conversion')
                    ->helperText('Default unit is always 1:1. Add other unit conversions here.'),

                Repeater::make('packagings')
                    ->label('Packagings (Larger Groupings)')
                    ->relationship('packagings')
                    ->hidden(fn (Get $get) => blank($get('unit_measure_id')))
                    ->schema([
                        TextInput::make('name')
                            ->label('Packaging Name (larger)')
                            ->required()
                            ->maxLength(100)
                            ->helperText('Describe the bigger container, e.g., Box, Sleeve, Bag, Blister.'),

                        TextInput::make('quantity')
                            ->label('Base Units inside this Package')
                            ->numeric()
                            ->minValue(0.01)
                            ->required()
                            ->rule('decimal:0,2')
                            ->step(0.01)
                            ->formatStateUsing(fn ($state) => $state === null
                                ? null
                                : ((float) $state == (int) $state
                                    ? (string) (int) $state
                                    : number_format((float) $state, 2, '.', '')))
                                ->helperText('Enter how many base units this package holds (e.g., 1 blister = 10 liters).'),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->collapsible()
                    ->addActionLabel('Add Packaging')
                    ->helperText('Use packagings to describe how base units are grouped (box, pack, sleeve, etc.).'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('unitMeasure.name')
                    ->label('Unit Measure')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('cost')
                    ->sortable()
                    ->money('PHP')
                    ->searchable(),

                TextColumn::make('defaultLocation.location')
                    ->label('Default Location')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('locationStocksSummary')
                    ->label('Location Stocks')
                    ->state(fn (Inventory $record) => self::renderLocationStocks($record))
                    ->html()
                    ->wrap(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->headerActions([
                ActionGroup::make([
                    Action::make('OpenCSVTemplate')
                        ->label('Open CSV Template')
                        ->icon('heroicon-m-document')
                        ->url(config('csv-template.templates.inventory'))
                        ->openUrlInNewTab(),

                    ImportAction::make('importInventories')
                        ->label('Import')
                        ->icon('heroicon-m-arrow-top-right-on-square')
                        ->importer(InventoryImporter::class)
                        ->after(function () {
                            Notification::make()
                                ->title('Inventory Imported')
                                ->body('Your CSV file has been successfully imported.')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'unitMeasure',
            'defaultLocation',
            'locationStocks.location',
        ]);
    }

    protected static function renderLocationStocks(Inventory $inventory): string
    {
        if ($inventory->locationStocks->isEmpty()) {
            return '<span class="text-sm text-gray-500">No stock recorded</span>';
        }

        $unitLabel = e($inventory->unitMeasure?->symbol ?? $inventory->unit_measure ?? '');

        return $inventory->locationStocks
            ->sortBy(fn (InventoryLocationStock $stock) => $stock->location?->location ?? '')
            ->map(function (InventoryLocationStock $stock) use ($unitLabel) {
                $locationName = e($stock->location?->location ?? 'Unassigned');
                $stockValue  = e(self::formatStock($stock->current_stock));
                $unitHtml    = $unitLabel ? "<span class=\"text-[11px] text-gray-500\"> {$unitLabel}</span>" : '';

                return "<div class=\"flex items-center justify-between gap-3\"><span>{$locationName}</span><span class=\"font-semibold\">{$stockValue}{$unitHtml}</span></div>";
            })
            ->implode('');
    }

    public static function formatStock(null|int|float|string $value): string
    {
        if ($value === null || $value === '') {
            return '0';
        }

        $formatted = number_format((float) $value, 4, '.', '');
        $trimmed   = rtrim(rtrim($formatted, '0'), '.');

        return $trimmed === '' ? '0' : $trimmed;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListInventories::route('/'),
            'create' => Pages\CreateInventory::route('/create'),
            'edit'   => Pages\EditInventory::route('/{record}/edit'),
            'view'   => Pages\ViewInventory::route('/{record}/view'),
        ];
    }
}
