<?php
namespace App\Filament\Tenant\Resources;

use App\Enums\VatType;
use App\Filament\Imports\ProductImporter;
use App\Filament\Tenant\Resources\ProductResource\Pages;
use App\Filament\Tenant\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

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

                Select::make('preparation_location_id')
                    ->relationship('preparationLocation', 'description')
                    ->nullable()
                    ->label('Preparation Location')
                    ->searchable()
                    ->preload()
                    ->placeholder('Select a preparation location')
                    ->createOptionForm([
                        TextInput::make('description')
                            ->required()
                            ->maxLength(255),

                        Toggle::make('printable')
                            ->label('Printable')
                            ->default(true),

                        Toggle::make('show_on_screen')
                            ->label('Show on Screen')
                            ->default(true),
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

                Toggle::make('multiple_packaging')
                    ->label('Multiple Packaging')
                    ->live(onBlur: true)
                    ->default(false),

                TextInput::make('price')
                    ->required()
                    ->default(0)
                    ->numeric()
                    ->label('Price')
                    ->hidden(fn(Get $get) => $get('multiple_packaging') === true),

                Select::make('vat_type')
                    ->label('Tax Type')
                    ->options([
                        VatType::VAT->value => VatType::VAT->getLabel(),
                        VatType::NON_VAT->value => VatType::NON_VAT->getLabel(),
                    ])
                    ->native(false)
                    ->searchable()
                    ->placeholder('Select VAT Type')
                    ->live(onBlur: true),

                Toggle::make('vat_inclusive')
                    ->label('Tax Inclusive')
                    ->default(false)
                    ->hidden(fn(Get $get) => $get('vat_type') !== VatType::VAT->value)
                    ->helperText('Check if Tax is included in the price'),

                TextInput::make('vat_rate')
                    ->label('Tax Rate (%)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->step(0.01)
                    ->default(0)
                    ->suffix('%')
                    ->hidden(fn(Get $get) => $get('vat_type') !== VatType::VAT->value)
                    ->helperText('Enter the Tax rate as a percentage'),

                TextInput::make('unit_measure')
                    ->label('Unit of Measure')
                    ->hidden(fn(Get $get) => $get('multiple_packaging') === true),

                Repeater::make('optionsPivot')
                    ->label('Product Options')
                    ->schema([
                        Select::make('option_id')
                            ->label('Option')
                            ->options(\App\Models\Option::pluck('option_name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->helperText(function (Get $get): ?HtmlString {
                                $optionId = $get('option_id');

                                if (! $optionId) {
                                    return null;
                                }

                                $option = \App\Models\Option::with(['optionItems.product', 'optionItems.productPackaging'])->find($optionId);

                                if (! $option || $option->optionItems->isEmpty()) {
                                    return new HtmlString('<span class="text-sm text-gray-500">No option items available</span>');
                                }

                                $items = $option->optionItems->map(function ($item) {
                                    $productName = $item->product?->name ?? 'Unknown';
                                    $packaging   = $item->productPackaging?->unit_measure ?? '';
                                    $price       = '₱' . number_format($item->price, 2);

                                    $itemText = $packaging
                                        ? "{$productName} ({$packaging}) - {$price}"
                                        : "{$productName} - {$price}";

                                    return "<li class='text-sm'>{$itemText}</li>";
                                })->join('');

                                return new HtmlString(
                                    "<div class='mt-2 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg'>
                                        <p class='text-sm font-medium text-gray-700 dark:text-gray-300 mb-2'>Available Option Items:</p>
                                        <ul class='list-disc list-inside text-gray-600 dark:text-gray-400 space-y-1'>{$items}</ul>
                                    </div>"
                                );
                            }),

                        TextInput::make('max_quantity')
                            ->label('Max Quantity')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->required()
                            ->helperText('Maximum quantity that can be selected'),

                        Toggle::make('is_default')
                            ->label('Is Default')
                            ->default(false)
                            ->helperText('Set as default option'),
                    ])
                    ->columns(3)
                    ->reorderable(false)
                    ->collapsible()
                    ->itemLabel(fn(array $state): ?string => \App\Models\Option::find($state['option_id'])?->option_name ?? null)
                    ->addActionLabel('Add Option')
                    ->columnSpanFull(),

                TextInput::make('total_onhand')
                    ->required()
                    ->default(0)
                    ->numeric()
                    ->label('Total Onhand'),

                RichEditor::make('description')
                    ->maxLength(500)
                    ->ColumnSpan(2)
                    ->label('Description'),

                Select::make('modifiers')
                    ->relationship('modifiers', 'name')
                    ->nullable()
                    ->searchable()
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
            ->defaultSort('name', 'asc')
            ->filters([
                SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Category'),

                SelectFilter::make('brand')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Brand'),

                SelectFilter::make('groups')
                    ->relationship('groups', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->label('Group'),
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
                    BulkAction::make('update_vat')
                        ->label('Update VAT Settings')
                        ->icon('heroicon-m-pencil-square')
                        ->form([
                            Select::make('vat_type')
                                ->label('Tax Type')
                                ->options([
                                    VatType::VAT->value => VatType::VAT->getLabel(),
                                    VatType::NON_VAT->value => VatType::NON_VAT->getLabel(),
                                ])
                                ->native(false)
                                ->searchable()
                                ->placeholder('Select Tax Type')
                                ->live(onBlur: true),

                            Toggle::make('vat_inclusive')
                                ->label('Tax Inclusive')
                                ->hidden(fn(Get $get) => $get('vat_type') !== VatType::VAT->value),

                            TextInput::make('vat_rate')
                                ->label('Tax Rate (%)')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(100)
                                ->step(0.01)
                                ->suffix('%')
                                ->hidden(fn(Get $get) => $get('vat_type') !== VatType::VAT->value),
                        ])
                        ->action(function (array $data, Tables\Actions\BulkAction $action) {
                            $records = $action->getRecords();

                            foreach ($records as $record) {
                                if (isset($data['vat_type'])) {
                                    $record->vat_type = $data['vat_type'];
                                }
                                if ($data['vat_type'] === VatType::VAT->value) {
                                    if (isset($data['vat_inclusive'])) {
                                        $record->vat_inclusive = $data['vat_inclusive'];
                                    }
                                    if (isset($data['vat_rate'])) {
                                        $record->vat_rate = $data['vat_rate'];
                                    }
                                }
                                $record->save();
                            }

                            Notification::make()
                                ->title('Tax Settings Updated')
                                ->body('Tax settings have been successfully updated for ' . count($records) . ' product(s).')
                                ->success()
                                ->send();
                        }),

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
