<?php
namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\InventoryLogResource\Pages;
use App\Models\InventoryLog;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InventoryLogResource extends Resource
{
    protected static ?string $model           = InventoryLog::class;
    protected static ?string $navigationGroup = "Inventory";
    protected static ?string $navigationIcon  = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('inventory_location_id')
                    ->relationship('inventoryLocation', 'location')
                    ->preload()
                    ->searchable(),

                TextInput::make('adjustment')
                    ->numeric()
                    ->required(),

                TextInput::make('new_balance')
                    ->numeric()
                    ->nullable(),

                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->preload()
                    ->searchable(),

                Select::make('approved_by')
                    ->label('Approved By')
                    ->relationship('approvedBy', 'name')
                    ->preload()
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('inventoryLocation.location')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('adjustment')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('new_balance')
                    ->label('New Balance')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('user.name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('approvedBy.name')
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
            'index'  => Pages\ListInventoryLogs::route('/'),
            'create' => Pages\CreateInventoryLog::route('/create'),
            'edit'   => Pages\EditInventoryLog::route('/{record}/edit'),
            'view'   => Pages\ViewInventoryLog::route('/{record}/view'),
        ];
    }
}
