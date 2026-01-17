<?php

namespace App\Filament\Tenant\Resources\ProductResource\RelationManagers;

use App\Models\ProductPackaging;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductAddOnsRelationManager extends RelationManager
{
    protected static string $relationship = 'productAddOns';

    protected static ?string $label = 'Product Add-ons';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('product_addon_id')
                    ->label('Add-on Product')
                    ->relationship('addonProduct', 'name', fn (Builder $query) => $query->orderBy('name'))
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('product_packaging_id', null))
                    ->required(),

                Select::make('product_packaging_id')
                    ->label('Add-on Packaging (Optional)')
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
                    ->nullable(),

                TextInput::make('add_on_price')
                    ->label('Add-on Price')
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0)
                    ->default(0)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('addonProduct.name')
                    ->label('Add-on Product')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('productPackaging.name')
                    ->label('Packaging')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('add_on_price')
                    ->label('Price')
                    ->sortable(),
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
