<?php

namespace App\Filament\Tenant\Resources\ProductOptionResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class OptionItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'optionItems';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('product_packaging_id')
                    ->label('Product Option Item')
                    ->relationship('productPackaging', 'unit_measure')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->unit_measure . ' - ' . $record->product->name)
                    ->options(function () {
                        $productOption = $this->getOwnerRecord();
                        //get the product of product packaging
                        $productId = $productOption->productPackaging->product_id ?? null;

                        // Show all product packagings, but only display the unit measure (no product name)
                        return \App\Models\ProductPackaging::where('product_id', '!=', $productId)
                            ->with('product')
                            ->get()
                            ->mapWithKeys(function ($packaging) {
                                return [$packaging->id => $packaging->product->name . ' - ' .$packaging->unit_measure  ];
                            });
                    }),


                Forms\Components\TextInput::make('additional_price')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('additional_price')
            ->columns([
                Tables\Columns\TextColumn::make('productPackaging.product.name')
                    ->label('Item Option')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('productPackaging.unit_measure')
                    ->label('Product Packaging')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('additional_price'),

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
