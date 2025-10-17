<?php
namespace App\Filament\Tenant\Resources\CartResource\RelationManagers;

use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class CartItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'cartItems';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'name', fn(Builder $query) => $query->withoutGlobalScopes([SoftDeletingScope::class]))
                    ->preload()
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

                Select::make('discount_id')
                    ->relationship('discount', 'discount_name')
                    ->preload()
                    ->searchable()
                    ->nullable(),

                TextInput::make('coupon_code')
                    ->label('Coupon Code')
                    ->nullable(),

                TextInput::make('sub_total')
                    ->label('Sub Total')
                    ->required()
                    ->numeric(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            // ->recordTitleAttribute('quantity')
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product')
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
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
