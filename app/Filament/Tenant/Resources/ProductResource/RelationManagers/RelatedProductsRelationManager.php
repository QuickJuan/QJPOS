<?php

namespace App\Filament\Tenant\Resources\ProductResource\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Product;

class RelatedProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'relatedProductLinks';

    protected static ?string $label = 'Related Products';

    public function form(Form $form): Form
    {
        $ownerId = (int) ($this->getOwnerRecord()?->id ?? 0);

        return $form
            ->schema([
                Select::make('related_product_id')
                    ->label('Related Product')
                    ->relationship('relatedProduct', 'name', function (Builder $query) use ($ownerId) {
                        if (! $ownerId) {
                            return $query->orderBy('name');
                        }

                        return $query
                            ->whereKeyNot($ownerId)
                            ->whereNotIn('products.id', function ($subquery) use ($ownerId) {
                                $subquery
                                    ->select('related_product_id')
                                    ->from('product_related_products')
                                    ->where('product_id', $ownerId);
                            })
                            ->orderBy('name');
                    })
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('relatedProduct.name')
            ->columns([
                TextColumn::make('relatedProduct.name')->label('Name')->searchable()->sortable(),
                TextColumn::make('relatedProduct.receipt_alias')->label('Receipt Name')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('relatedProduct.price')->label('Price')->sortable(),
                TextColumn::make('relatedProduct.is_active')->label('Active')->badge()->formatStateUsing(fn (bool $state) => $state ? 'Yes' : 'No')
                    ->color(fn (bool $state) => $state ? 'success' : 'gray'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Related Product'),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
