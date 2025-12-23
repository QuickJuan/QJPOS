<?php
namespace App\Filament\Tenant\Resources\ProductResource\RelationManagers;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ProductPackagingsRelationManager extends RelationManager
{
    protected static string $relationship      = 'productPackagings';
    protected static ?string $label            = "Product Pricing";
    protected static ?string $modelLabel       = 'Product Pricing';
    protected static ?string $pluralModelLabel = 'Product Pricings';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Product Pricing';
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->product_type === 'with_variant';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Name')
                    ->required(),

                TextInput::make('unit_measure')
                    ->required()
                    ->maxLength(50),
                TextInput::make('cost')
                    ->required()
                    ->numeric()
                    ->label('Unit Cost'),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->label('Unit Price'),
                TextInput::make('qty')
                    ->required()
                    ->numeric()
                    ->label('Quantity'),
                SpatieMediaLibraryFileUpload::make('featured_image')
                    ->collection('featured_image')
                    ->image()
                    ->label('Featured Image'),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('unit_measure')
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('unit_measure'),

                TextColumn::make('cost')
                    ->money('php', true)
                    ->label('Unit Cost'),

                TextColumn::make('price')
                    ->money('php', true)
                    ->label('Unit Price'),

                TextColumn::make('qty')
                    ->label('Quantity'),
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
