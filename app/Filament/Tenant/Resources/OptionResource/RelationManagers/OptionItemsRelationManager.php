<?php
namespace App\Filament\Tenant\Resources\OptionResource\RelationManagers;

use Filament\Tables;
use App\Models\Option;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ProductPackaging;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;

class OptionItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'optionItems';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('product_packaging_id')
                    ->label('Product Option Item')
                    ->relationship('productPackaging', 'unit_measure')
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->unit_measure . ' - ' . $record->product->name)
                    ->options(function ($get) {
                        $optionId = $this->getOwnerRecord()->id;

                        if (! $optionId) {
                            return [];
                        }

                        $option    = Option::find($optionId);
                        $productId = $option?->productPackaging?->product_id;

                        return ProductPackaging::where('product_id', '!=', $productId)
                            ->with('product')
                            ->get()
                            ->mapWithKeys(fn($packaging) => [$packaging->id => $packaging->product->name . ' - ' . $packaging->unit_measure]);
                    }),

                TextInput::make('price')
                    ->label('Price')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('price')
            ->columns([
                TextColumn::make('product.name')
                    ->label('Main Product')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('productPackaging.product.name')
                    ->label('Item Option')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('productPackaging.unit_measure')
                    ->label('Product Packaging')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('price')
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
