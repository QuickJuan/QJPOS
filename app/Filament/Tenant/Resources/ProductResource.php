<?php
namespace App\Filament\Tenant\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use App\Filament\Imports\ProductImporter;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Actions\ImportAction;
use App\Filament\Tenant\Resources\ProductResource\Pages;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use App\Filament\Tenant\Resources\ProductResource\RelationManagers;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
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


                TextInput::make('uuid')
                    ->required()
                    ->maxLength(150)
                    ->label('UUID')
                    ->default(fn () => (string) \Illuminate\Support\Str::uuid())
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->headerActions([
                ActionGroup::make([
                    Action::make('OpenCSVTemplate')
                        ->label('Open CSV Template')
                        ->icon('heroicon-m-document')
                        ->url(config('csv-template.templates.product'))
                        ->openUrlInNewTab(),

                    ImportAction::make('importProducts')
                        ->label('Import')
                        ->icon('heroicon-m-arrow-top-right-on-square')
                        ->importer(ProductImporter::class)
                        ->after(function () {
                            Notification::make()
                                ->title('Product Imported')
                                ->body('Your CSV file has been successfully imported.')
                                ->success()
                                ->send();
                        }),
                ]),
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
            'view'   => Pages\ViewProduct::route('/{record}/view'),
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
