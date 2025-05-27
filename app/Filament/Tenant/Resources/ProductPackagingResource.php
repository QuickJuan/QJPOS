<?php
namespace App\Filament\Tenant\Resources;

use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ProductPackaging;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use App\Filament\Tenant\Resources\ProductPackagingResource\Pages;
use App\Filament\Tenant\Resources\ProductPackagingResource\RelationManagers\ProductOptionsRelationManager;

class ProductPackagingResource extends Resource
{
    protected static ?string $model = ProductPackaging::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('product_id')
                    ->relationship('product', 'name'),

                TextInput::make('cost')
                    ->label('Cost')
                    ->numeric()
                    ->required(),

                TextInput::make('price')
                    ->label('Price')
                    ->numeric()
                    ->required(),

                TextInput::make('unit_measure')
                    ->label('Unit Measure')
                    ->required(),

                TextInput::make('qty')
                    ->label('Quantity')
                    ->numeric()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('unit_measure')
                    ->label('Unit Measure')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('cost')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('price')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('qty')
                    ->label('Quantity')
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
            ProductOptionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProductPackagings::route('/'),
            'create' => Pages\CreateProductPackaging::route('/create'),
            'edit'   => Pages\EditProductPackaging::route('/{record}/edit'),
        ];
    }
}
