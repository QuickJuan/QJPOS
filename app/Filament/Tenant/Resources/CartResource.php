<?php
namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\CartResource\Pages;
use App\Filament\Tenant\Resources\CartResource\RelationManagers\CartItemsRelationManager;
use App\Models\Cart;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CartResource extends Resource
{
    protected static ?string $model           = Cart::class;
    protected static ?string $navigationGroup = "Cart";
    protected static ?string $navigationIcon  = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('cashier_id')
                    ->label('Cashier')
                    ->relationship('cashier', 'name')
                    ->required()
                    ->preload()
                    ->searchable(),

                Select::make('cashier_session_id')
                    ->label('Cashier Session')
                    ->relationship('cashierSession', 'beginning_cash')
                    ->required()
                    ->preload()
                    ->searchable(),

                Textarea::make('notes')
                    ->label('Notes')
                    ->nullable(),

                KeyValue::make('meta_data')
                    ->label('Meta Data')
                    ->keyLabel('Key')
                    ->valueLabel('Value')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cashier.name')
                    ->label('Cashier')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('cashierSession.beginning_cash')
                    ->label('Cashier Session')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(50)
                    ->sortable()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
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
            CartItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCarts::route('/'),
            'create' => Pages\CreateCart::route('/create'),
            'edit'   => Pages\EditCart::route('/{record}/edit'),
            'view'   => Pages\ViewCart::route('/{record}/view'),
        ];
    }
}
