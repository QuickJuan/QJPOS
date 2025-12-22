<?php
namespace App\Filament\Tenant\Resources;

use App\Filament\Imports\InventoryImporter;
use App\Filament\Tenant\Resources\InventoryResource\Pages;
use App\Models\Inventory;
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
                    ->label('Unit Conversions')
                    ->relationship('unitConversions')
                    ->hidden(fn (Get $get) => blank($get('unit_measure_id')))
                    ->schema([
                        Select::make('unit_measure_id')
                            ->label('Conversion Unit')
                            ->relationship('unitMeasure', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('Choose an alternate unit for this inventory item.'),

                        TextInput::make('conversion_factor')
                            ->label('Base Units per Conversion')
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
                            ->helperText('How many base units equal 1 of the selected unit.'),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->collapsible()
                    ->addActionLabel('Add Conversion')
                    ->helperText('Default unit is always 1:1. Add other unit conversions here.'),

                Repeater::make('packagings')
                    ->label('Packagings')
                    ->relationship('packagings')
                    ->hidden(fn (Get $get) => blank($get('unit_measure_id')))
                    ->schema([
                        TextInput::make('name')
                            ->label('Packaging Name')
                            ->required()
                            ->maxLength(100)
                            ->helperText('e.g., Box, Sleeve, Bag'),

                        TextInput::make('quantity')
                            ->label('Base Units per Package')
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
                            ->helperText('How many base units make up this packaging (e.g., 24 pcs per box).'),
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
