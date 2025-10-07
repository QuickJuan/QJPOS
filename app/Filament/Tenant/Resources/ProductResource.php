<?php
namespace App\Filament\Tenant\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use App\Filament\Tenant\Resources\ProductResource\Pages;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use App\Filament\Tenant\Resources\ProductResource\RelationManagers;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('uuid')
                    ->required()
                    ->maxLength(150)
                    ->label('UUID'),

                TextInput::make('name')
                    ->required()
                    ->maxLength(150)
                    ->label('Product Name'),

                TextInput::make('receipt_alias')
                    ->required()
                    ->maxLength(150)
                    ->label('Receipt Name'),

                Select::make('options')
                    ->relationship('options', 'option_name')
                    ->multiple()
                    ->searchable()
                    ->preload(),

                RichEditor::make('description')
                    ->maxLength(500)
                    ->ColumnSpan(2)
                    ->label('Description'),

                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required()
                    ->label('Category')
                    ->searchable()
                    ->preload()
                    ->placeholder('Select a category')
                    ->createOptionForm([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Category Name'),
                    ]),

                Select::make('brand_id')
                    ->relationship('brand', 'name')
                    ->required()
                    ->label('Brand')
                    ->searchable()
                    ->preload()
                    ->placeholder('Select a brand')
                    ->createOptionForm([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Brand Name'),
                    ]),

                Select::make('groups')
                    ->relationship('groups', 'name')
                    ->multiple()
                    ->preload(),

                SpatieMediaLibraryFileUpload::make('featured_image')
                    ->label('Featured Image')
                    ->collection('featured_image')
                    ->image()
                    ->imageEditor(),

                SpatieMediaLibraryFileUpload::make('product_images')
                    ->label('Product Images')
                    ->collection('product_images')
                    ->multiple()
                    ->image()
                    ->imageEditor(),

            ])->columns(2)
            ->columns([
                'sm' => 1,
                'md' => 2,
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('featured_image')
                    ->collection('featured_image')
                    ->circular(),

                TextColumn::make('name')
                    ->label('Product Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('receipt_alias')
                    ->label('Receipt Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('brand.name')
                    ->label('Brand')
                    ->searchable()
                    ->sortable(),

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
            // RelationManagers\CategoryRelationManager::class,
            // RelationManagers\BrandRelationManager::class,
            RelationManagers\ProductPackagingsRelationManager::class,
            // Add other relation managers here if needed
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Products';
    }

    public static function getNavigationSort(): ?int
    {
        return 4; // First in group
    }
}
