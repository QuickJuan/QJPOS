<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\ProductAddOnResource\Pages;
use App\Models\ProductAddOn;
use App\Models\ProductPackaging;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductAddOnResource extends Resource
{
    protected static ?string $model = ProductAddOn::class;

    protected static ?string $navigationLabel = 'Product Add-ons';
    protected static ?string $navigationIcon = 'heroicon-o-squares-plus';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('product_id')
                    ->label('Main Product')
                    ->relationship('product', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('product_addon_id')
                    ->label('Add-on Product')
                    ->relationship('addonProduct', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('product_packaging_id', null)),

                Select::make('product_packaging_id')
                    ->label('Add-on Packaging')
                    ->nullable()
                    ->searchable()
                    ->options(function (Get $get): array {
                        $addonProductId = $get('product_addon_id');
                        if (! $addonProductId) {
                            return [];
                        }

                        return ProductPackaging::query()
                            ->where('product_id', $addonProductId)
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(function (ProductPackaging $packaging) {
                                $label = trim(($packaging->name ?: $packaging->unit_measure) ?? '');
                                $price = $packaging->price ?? null;
                                if ($price !== null && $price !== '') {
                                    $label .= " (₱{$price})";
                                }

                                return [$packaging->id => $label];
                            })
                            ->toArray();
                    })
                    ->helperText('Optional. Select this when the add-on product has multiple packagings.'),

                TextInput::make('add_on_price')
                    ->label('Add-on Price')
                    ->numeric()
                    ->step(0.01)
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

                TextColumn::make('addonProduct.name')
                    ->label('Add-on Product')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('productPackaging.unit_measure')
                    ->label('Add-on Packaging')
                    ->toggleable()
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('add_on_price')
                    ->label('Add-on Price')
                    ->sortable(),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductAddOns::route('/'),
            'create' => Pages\CreateProductAddOn::route('/create'),
            'edit' => Pages\EditProductAddOn::route('/{record}/edit'),
            'view' => Pages\ViewProductAddOn::route('/{record}'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Products';
    }

    public static function getNavigationSort(): ?int
    {
        return 6;
    }
}
