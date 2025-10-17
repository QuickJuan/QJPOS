<?php
namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\LocationResource\Pages;
use App\Models\Location;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LocationResource extends Resource
{
    protected static ?string $model           = Location::class;
    protected static ?string $navigationGroup = "Inventory";
    protected static ?string $navigationIcon  = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('location')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('location')
                    ->sortable()
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
            'index'  => Pages\ListLocations::route('/'),
            'create' => Pages\CreateLocation::route('/create'),
            'edit'   => Pages\EditLocation::route('/{record}/edit'),
            'view'   => Pages\ViewLocation::route('/{record}/view'),
        ];
    }
}
