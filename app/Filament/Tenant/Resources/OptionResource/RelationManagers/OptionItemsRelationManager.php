<?php
namespace App\Filament\Tenant\Resources\OptionResource\RelationManagers;

use App\Models\Product;
use App\Models\ProductPackaging;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OptionItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'optionItems';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('product_id')
                    ->label('Option Item ')
                    ->relationship('product', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        // Reset product_packaging_id when product changes
                        $set('product_packaging_id', null);
                    }),

                Select::make('product_packaging_id')
                    ->label('Product Packaging')
                    ->searchable()
                    ->preload()
                    ->options(function (Get $get) {
                        $productId = $get('product_id');

                        if (! $productId) {
                            return [];
                        }

                        return ProductPackaging::where('product_id', $productId)
                            ->get()
                            ->mapWithKeys(fn($packaging) => [
                                $packaging->id => $packaging->name . ' - ' . $packaging->qty . ' ' . $packaging->unit_measure,
                            ]);
                    })
                    ->visible(function (Get $get) {
                        $productId = $get('product_id');

                        if (! $productId) {
                            return false;
                        }

                        $product = Product::find($productId);
                        return $product?->multiple_packaging === true;
                    })
                    ->required(function (Get $get) {
                        $productId = $get('product_id');

                        if (! $productId) {
                            return false;
                        }

                        $product = Product::find($productId);
                        return $product?->multiple_packaging === true;
                    })
                    ->dehydrated(function (Get $get) {
                        $productId = $get('product_id');

                        if (! $productId) {
                            return false;
                        }

                        $product = Product::find($productId);
                        return $product?->multiple_packaging === true;
                    }),

                TextInput::make('price')
                    ->label('Additional Price')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->default(0)
                    ->prefix('₱')
                    ->helperText('Additional price for this option item'),

                TextInput::make('quantity')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->default(0)
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('price')
            ->columns([
                TextColumn::make('product.name')
                    ->label('Option Item')
                    ->sortable()
                    ->searchable()
                    ->description(fn($record) => $record->product?->multiple_packaging ? $record->productPackaging->name : $record->product?->unit_measure),

                TextColumn::make('productPackaging.unit_measure')
                    ->label('Packaging')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn($record) => $record->productPackaging ? $record->productPackaging->qty . ' ' . $record->productPackaging->unit_measure : 'N/A'),

                TextColumn::make('price')
                    ->label('Additional Price')
                    ->money('PHP')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('quantity')
                    ->sortable()
                    ->searchable(),
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
