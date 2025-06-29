<?php

namespace App\Filament\Tenant\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\CartItem;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Tenant\Resources\CartItemResource\Pages;
use App\Filament\Tenant\Resources\CartItemResource\RelationManagers;

class CartItemResource extends Resource
{
    protected static ?string $model = CartItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('cart_id')
                    ->label('Cart')
                    ->relationship('cart', 'id', fn (Builder $query) => $query->withoutGlobalScopes([SoftDeletingScope::class]))
                    ->searchable()
                    ->required(),

                Select::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'name', fn (Builder $query) => $query->withoutGlobalScopes([SoftDeletingScope::class]))
                    ->searchable()
                    ->required(),

                Select::make('product_packaging_id')
                    ->label('Product Packaging')
                    ->relationship('productPackaging', 'name', fn (Builder $query) => $query->withoutGlobalScopes([SoftDeletingScope::class]))
                    ->searchable()
                    ->required(),

                TextInput::make('quantity')
                    ->label('Quantity')
                    ->required()
                    ->numeric(),

                TextInput::make('price')
                    ->label('Price')
                    ->required()
                    ->numeric(),

                TextInput::make('amount')
                    ->label('Amount')
                    ->required()
                    ->numeric(),

                TextInput::make('discount')
                    ->label('Discount')
                    ->nullable()
                    ->numeric(),

                TextInput::make('discount_id')
                    ->label('Discount ID')
                    ->nullable()
                    ->numeric(),

                TextInput::make('coupon_code')
                    ->label('Coupon Code')
                    ->nullable(),

                TextInput::make('sub_total')
                    ->label('Sub Total')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cart_id')
                    ->label('Cart')
                    ->searchable()
                    ->sortable()
                    ->relationship('cart', 'id'),

                TextColumn::make('product_id')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->relationship('product', 'name'),

                TextColumn::make('product_packaging_id')
                    ->label('Product Packaging')
                    ->searchable()
                    ->sortable()
                    ->relationship('productPackaging', 'name'),

                TextColumn::make('quantity')
                    ->label('Quantity')
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Price')
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->sortable(),

                TextColumn::make('discount')
                    ->label('Discount'),

                TextColumn::make('discount_id')
                    ->label('Discount ID'),

                TextColumn::make('coupon_code')
                    ->label('Coupon Code'),

                TextColumn::make('sub_total')
                    ->label('Sub Total')
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
            'index' => Pages\ListCartItems::route('/'),
            'create' => Pages\CreateCartItem::route('/create'),
            'edit' => Pages\EditCartItem::route('/{record}/edit'),
        ];
    }
}
