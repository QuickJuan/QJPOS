<?php
namespace App\Filament\Tenant\Resources;

use Filament\Forms;
use App\Models\Cart;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Tenant\Resources\CartResource\Pages;

class CartResource extends Resource
{
    protected static ?string $model = Cart::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->required()
                    ->searchable(),

                Select::make('cashier_shift_id')
                    ->label('Cashier Shift')
                    ->relationship('cashierShift', 'name')
                    ->required()
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
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('cashierShift.name')
                    ->label('Cashier Shift')
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
                Tables\Actions\EditAction::make(),
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
            'index'  => Pages\ListCarts::route('/'),
            'create' => Pages\CreateCart::route('/create'),
            'edit'   => Pages\EditCart::route('/{record}/edit'),
        ];
    }
}
