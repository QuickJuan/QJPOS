<?php
namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\ProductPackagingResource\Pages;
use App\Models\ProductPackaging;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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

                TextColumn::make('cost')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('price')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('unit_measure')
                    ->label('Unit Measure')
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
            //
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
