<?php
namespace App\Filament\Tenant\Resources;

use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ProductOption;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Checkbox;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use App\Filament\Tenant\Resources\ProductOptionResource\Pages;
use App\Filament\Tenant\Resources\ProductOptionResource\RelationManagers\OptionItemsRelationManager;

class ProductOptionResource extends Resource
{
    protected static ?string $model = ProductOption::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('product_packaging_id')
                    ->label('Product Packaging')
                    ->relationship('productPackaging', 'unit_measure')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->product->name . ' - ' . $record->unit_measure),

                TextInput::make('name')
                    ->label('Option Name')
                    ->helperText('This is the name of the product option. sample Drinks, Side dish')
                    ->required(),

                TextInput::make('qty')
                    ->label('Limit quantity')
                    ->numeric()
                    ->required(),

                Checkbox::make('is_default')
                    ->label('Is Default')
                    ->helperText('Toggle this option if the option is default for the product packaging')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('productPackaging')
                    ->label('Product & Packaging')
                    ->formatStateUsing(fn ($record) => $record->productPackaging?->product?->name . ' - ' . $record->productPackaging?->unit_measure)
                    ->sortable()
                    ->searchable(),

               TextColumn::make('name')
                    ->label('Option Name')
                    ->sortable()
                    ->searchable(),

               TextColumn::make('qty')
                    ->label('Limit quantity')
                    ->sortable(),

               IconColumn::make('is_default')
                    ->label('Is Default')
                    ->boolean(),
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
            OptionItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProductOptions::route('/'),
            'create' => Pages\CreateProductOption::route('/create'),
            'edit'   => Pages\EditProductOption::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationGroup(): ?string
    {
        return 'Products';
    }

    public static function getNavigationSort(): ?int
    {
        return 6; // Second in group
    }
}
