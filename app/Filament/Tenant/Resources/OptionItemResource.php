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
                    ->preload(),

                Select::make('product_packaging_id')
                    ->label('Product Packaging')
                    ->relationship('productPackaging', 'unit_measure')
                    ->required()
                    ->searchable()
                    ->preload(),

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
                //
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
