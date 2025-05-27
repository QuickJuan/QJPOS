<?php
namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\OptionItemResource\Pages;
use App\Models\OptionItem;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OptionItemResource extends Resource
{
    protected static ?string $model = OptionItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('product_option_id')
                    ->label('Product Option')
                    ->relationship('productOption', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        $optionName = $record->name ?? '';
                        $productName = $record->productPackaging?->product?->name ?? '';
                        $unitMeasure = $record->productPackaging?->unit_measure ?? '';
                        return trim(  $productName . ' ' . $unitMeasure . ' - ' .$optionName);
                    }),

                Select::make('product_packaging_id')
                    ->label('Product Option Item')
                    ->relationship('productPackaging', 'unit_measure')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->unit_measure . ' - ' . $record->product->name)
                    ->options(function ($get) {
                        $productOptionId = $get('product_option_id');
                        if (!$productOptionId) {
                            return [];
                        }
                        $productOption = \App\Models\ProductOption::find($productOptionId);
                        $productId = $productOption?->productPackaging?->product_id;
                        return \App\Models\ProductPackaging::where('product_id', '!=', $productId)
                            ->with('product')
                            ->get()
                            ->mapWithKeys(function ($packaging) {
                                return [$packaging->id => $packaging->product->name . ' - ' . $packaging->unit_measure];
                            });
                    }),

                TextInput::make('additional_price')
                    ->label('Additional Price')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('productOption.productPackaging.product.name')
                    ->label('Main Product')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('productOption.name')
                    ->label('Product Option')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('productPackaging.product.name')
                    ->label('Item Option')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('productPackaging.unit_measure')
                    ->label('Product Packaging')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('additional_price')
                    ->sortable()
                    ->searchable(),
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
            'index'  => Pages\ListOptionItems::route('/'),
            'create' => Pages\CreateOptionItem::route('/create'),
            'edit'   => Pages\EditOptionItem::route('/{record}/edit'),
        ];
    }
}
