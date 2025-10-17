<?php
namespace App\Filament\Tenant\Resources\BrandResource\RelationManagers;

use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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

                Select::make('groups')
                    ->relationship('groups', 'name')
                    ->multiple()
                    ->preload(),

                TextInput::make('uuid')
                    ->required()
                    ->maxLength(150)
                    ->label('UUID')
                    ->default(fn() => (string) \Illuminate\Support\Str::uuid())
                    ->disabled()
                    ->dehydrated(),

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
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
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
