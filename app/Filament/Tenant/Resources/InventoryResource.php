<?php
namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\InventoryResource\Pages;
use App\Models\Inventory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
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

                TextInput::make('unit_measure')
                    ->label('Unit Measure')
                    ->required(),

                TextInput::make('cost')
                    ->numeric()
                    ->prefix('₱')
                    ->rules(['numeric'])
                    ->required(),

                Select::make('default_location')
                    ->relationship('defaultLocation', 'location')
                    ->preload()
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('unit_measure')
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
