<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\OrderItemResource\Pages;
use App\Models\OrderItem;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderItemResource extends Resource
{
    protected static ?string $model                 = OrderItem::class;
    protected static ?string $navigationGroup       = "Order";
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $navigationIcon        = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('order_id')
                    ->label('Order')
                    ->relationship('order', 'id', fn(Builder $query) => $query->withoutGlobalScopes())
                    ->preload()
                    ->searchable()
                    ->required(),

                Select::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'name', fn(Builder $query) => $query->withoutGlobalScopes())
                    ->preload()
                    ->searchable()
                    ->required(),

                Select::make('product_packaging_id')
                    ->label('Product Packaging')
                    ->relationship('productPackaging', 'name', fn(Builder $query) => $query->withoutGlobalScopes())
                    ->nullable()
                    ->preload()
                    ->searchable(),

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

                TextInput::make('order_type')
                    ->label('Order Type')
                    ->nullable(),

                TextInput::make('discount')
                    ->label('Discount')
                    ->nullable()
                    ->numeric(),

                Select::make('discount_id')
                    ->label('Discount')
                    ->relationship('discount', 'discount_name', fn(Builder $query) => $query->withoutGlobalScopes())
                    ->nullable()
                    ->preload()
                    ->searchable(),

                TextInput::make('coupon_code')
                    ->label('Coupon Code')
                    ->nullable(),

                TextInput::make('sub_total')
                    ->label('Sub Total')
                    ->required()
                    ->numeric(),

                Toggle::make('is_served')
                    ->label('Is Served')
                    ->default(false),

                Toggle::make('is_void')
                    ->label('Is Void')
                    ->default(false),

                TextInput::make('reason')
                    ->label('Reason')
                    ->nullable(),

                KeyValue::make('selected_options')
                    ->label('Selected Options')
                    ->keyLabel('Key')
                    ->valueLabel('Value')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.id')
                    ->label('Order')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('productPackaging.name')
                    ->label('Product Packaging')
                    ->searchable()
                    ->sortable(),

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

                TextColumn::make('discount.discount_name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('coupon_code')
                    ->label('Coupon Code'),

                TextColumn::make('sub_total')
                    ->label('Sub Total')
                    ->sortable(),

                Tables\Columns\BooleanColumn::make('is_served')
                    ->label('Is Served'),

                Tables\Columns\BooleanColumn::make('is_void')
                    ->label('Is Void'),
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
            'index'  => Pages\ListOrderItems::route('/'),
            'create' => Pages\CreateOrderItem::route('/create'),
            'edit'   => Pages\EditOrderItem::route('/{record}/edit'),
        ];
    }
}
