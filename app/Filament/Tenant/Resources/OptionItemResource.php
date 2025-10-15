<?php
namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\OptionItemResource\Pages;
use App\Models\Option;
use App\Models\OptionItem;
use App\Models\ProductPackaging;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OptionItemResource extends Resource
{
    protected static ?string $model = OptionItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('option_id')
                    ->label('Option')
                    ->relationship('option', 'option_name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        $optionName  = $record->option_name ?? '';
                        $productName = $record->productPackaging?->product?->name ?? '';
                        $unitMeasure = $record->productPackaging?->unit_measure ?? '';
                        return trim("{$productName} {$unitMeasure} - {$optionName}");
                    }),

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
                        $optionId = $get('option_id');

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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Main Product')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('option.option_name')
                    ->label('Product Option')
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

    public static function getNavigationGroup(): ?string
    {
        return 'Products';
    }

    public static function getNavigationSort(): ?int
    {
        return 8; // Second in group
    }
}
