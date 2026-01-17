<?php
namespace App\Filament\Tenant\Resources;

use App\Enums\Product\Type as ProductType;
use App\Enums\VatType;
use App\Filament\Imports\ProductImporter;
use App\Filament\Tenant\Resources\ProductResource\Pages;
use App\Filament\Tenant\Resources\ProductResource\RelationManagers;
use App\Filament\Tenant\Resources\ProductResource\RelationManagers\OptionsRelationManager;
use App\Models\Product;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Hidden;
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

                Select::make('product_type')
                    ->label('Product Type')
                    ->required()
                    ->options([
                        ProductType::SIMPLE->value => 'Simple',
                        ProductType::WITH_VARIANT->value => 'With Variant',
                        ProductType::COMPOSITE->value => 'Composite',
                        ProductType::BUNDLE->value => 'Bundle',
                    ])
                    ->default(ProductType::SIMPLE->value)
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state !== ProductType::SIMPLE->value) {
                            $set('track_inventory', false);
                        }
                    })
                    ->helperText('Choose how this product behaves in POS and inventory.'),

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

                TextInput::make('price')
                    ->required()
                    ->default(0)
                    ->numeric()
                    ->label('Price')
                    ->hidden(fn(Get $get) => $get('product_type') === ProductType::WITH_VARIANT->value),

                TextInput::make('barcode')
                    ->label('Barcode')
                    ->maxLength(150)
                    ->unique(ignoreRecord: true)
                    ->hidden(fn(Get $get) => $get('product_type') === ProductType::WITH_VARIANT->value)
                    ->helperText('Barcode for quick product lookup. Not available for products with variants.'),

                Toggle::make('track_inventory')
                    ->label('Track Inventory')
                    ->helperText(function (Get $get) {
                        $message = 'Enable to automatically manage stock for this simple product.';

                        if (! $get('track_inventory') && $get('has_inventory_links')) {
                            $message .= ' While tracking is off, linked inventories remain hidden and sales will not reduce stock.';
                        }

                        return $message;
                    })
                    ->reactive()
                    ->live()
                    ->inline(false)
                    ->default(false)
                    ->afterStateUpdated(function ($state, callable $set, callable $get, Component $component) {
                        if ($state) {
                            return;
                        }

                        $livewire = $component->getLivewire();

                        if (! method_exists($livewire, 'getRecord')) {
                            return;
                        }

                        $record = $livewire->getRecord();

                        if (! $record || ! $record->inventoryRecipes()->exists()) {
                            return;
                        }

                        Notification::make()
                            ->title('Inventory tracking disabled')
                            ->body('Existing inventory links are now hidden and future sales will skip stock deduction until tracking is enabled again.')
                            ->info()
                            ->send();

                        $set('has_inventory_links', true);
                    })
                    ->disabled(fn (Get $get) => $get('product_type') !== ProductType::SIMPLE->value),

                Hidden::make('has_inventory_links')
                    ->default(fn (?Product $record) => $record?->inventoryRecipes()->exists())
                    ->dehydrated(false),

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
                    ->hidden(fn(Get $get) => $get('product_type') === ProductType::WITH_VARIANT->value),

                Repeater::make('inventoryRecipes')
                    ->label('Recipe Ingredients')
                    ->relationship('inventoryRecipes')
                    ->hidden(fn(Get $get) => $get('product_type') !== ProductType::COMPOSITE->value)
                    ->schema([
                        Select::make('inventory_id')
                            ->label('Inventory Item')
                            ->relationship('inventory', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (! $state) {
                                    $set('unit_measure', null);
                                    $set('unit_option', null);
                                    $set('use_custom_unit', false);
                                    $set('unit_type', 'base');
                                    $set('unit_reference_id', null);
                                    return;
                                }

                                $inventory = \App\Models\Inventory::find($state);
                                $set('unit_option', null);
                                $set('use_custom_unit', false);
                                $set('unit_type', 'base');
                                $set('unit_reference_id', null);
                                $set('unit_measure', $inventory?->unit_measure);
                            }),

                        TextInput::make('quantity')
                            ->label('Qty Used')
                            ->numeric()
                            ->minValue(0)
                            ->required(),

                        Toggle::make('use_custom_unit')
                            ->label('Use Conversion / Packaging')
                            ->dehydrated(false)
                            ->reactive()
                            ->hidden(fn (Get $get) => blank($get('inventory_id')))
                            ->helperText('Enable to deduct via a conversion unit or packaging.')
                            ->default(false)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if (! $state) {
                                    $inventoryId = $get('inventory_id');
                                    $inventory   = $inventoryId ? \App\Models\Inventory::find($inventoryId) : null;
                                    $set('unit_option', null);
                                    $set('unit_type', 'base');
                                    $set('unit_reference_id', null);
                                    $set('unit_measure', $inventory?->unit_measure);
                                }
                            }),

                        Select::make('unit_option')
                            ->label('Select Conversion / Packaging')
                            ->dehydrated(false)
                            ->reactive()
                            ->hidden(fn (Get $get) => blank($get('inventory_id')) || ! $get('use_custom_unit'))
                            ->required(fn (Get $get) => (bool) $get('use_custom_unit'))
                            ->placeholder('Choose an available conversion or packaging')
                            ->options(function (Get $get) {
                                $inventoryId = $get('inventory_id');

                                if (! $inventoryId) {
                                    return [];
                                }

                                $inventory = \App\Models\Inventory::with([
                                    'unitConversions.unitMeasure',
                                    'packagings',
                                ])->find($inventoryId);

                                if (! $inventory) {
                                    return [];
                                }

                                $options = [];

                                if ($inventory->unit_measure) {
                                    $options['base:0'] = 'Base Unit - ' . $inventory->unit_measure;
                                }

                                foreach ($inventory->unitConversions as $conversion) {
                                    $unitName = $conversion->unitMeasure?->name ?? 'Conversion';
                                    $factor = $conversion->conversion_factor ?? 0;
                                    $formattedFactor = rtrim(rtrim(number_format($factor, 4, '.', ''), '0'), '.');
                                    $options['conversion:' . $conversion->id] = sprintf(
                                        'Conversion - %s (1 %s = %s %s)',
                                        $unitName,
                                        $unitName,
                                        $formattedFactor ?: '0',
                                        $inventory->unit_measure ?? 'base units'
                                    );
                                }

                                foreach ($inventory->packagings as $packaging) {
                                    $qty = $packaging->quantity ?? 0;
                                    $formattedQty = rtrim(rtrim(number_format($qty, 4, '.', ''), '0'), '.');
                                    $options['packaging:' . $packaging->id] = sprintf(
                                        'Packaging - %s (%s %s per package)',
                                        $packaging->name,
                                        $formattedQty ?: '0',
                                        $inventory->unit_measure ?? 'base units'
                                    );
                                }

                                return $options;
                            })
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $inventoryId = $get('inventory_id');

                                if (! $inventoryId) {
                                    $set('unit_measure', null);
                                    $set('unit_type', 'base');
                                    $set('unit_reference_id', null);
                                    return;
                                }

                                $inventory = \App\Models\Inventory::with([
                                    'unitConversions.unitMeasure',
                                    'packagings',
                                ])->find($inventoryId);

                                if (! $inventory) {
                                    $set('unit_measure', null);
                                    $set('unit_type', 'base');
                                    $set('unit_reference_id', null);
                                    return;
                                }

                                if (! $state || $state === 'base:0') {
                                    $set('unit_type', 'base');
                                    $set('unit_reference_id', null);
                                    $set('unit_measure', $inventory->unit_measure);
                                    return;
                                }

                                $parts = explode(':', $state);
                                $type  = $parts[0] ?? null;
                                $id    = isset($parts[1]) ? (int) $parts[1] : null;

                                if ($type === 'conversion' && $id) {
                                    $conversion = $inventory->unitConversions->firstWhere('id', $id);
                                    $set('unit_type', 'conversion');
                                    $set('unit_reference_id', $conversion?->id);
                                    $set('unit_measure', $conversion?->unitMeasure?->name ?? $inventory->unit_measure);
                                    return;
                                }

                                if ($type === 'packaging' && $id) {
                                    $packaging = $inventory->packagings->firstWhere('id', $id);
                                    $set('unit_type', 'packaging');
                                    $set('unit_reference_id', $packaging?->id);
                                    $set('unit_measure', $packaging?->name ?? $inventory->unit_measure);
                                    return;
                                }

                                $set('unit_type', 'base');
                                $set('unit_reference_id', null);
                                $set('unit_measure', $inventory->unit_measure);
                            }),

                        TextInput::make('unit_measure')
                            ->label('Unit (auto-filled)')
                            ->maxLength(50)
                            ->placeholder('Defaults to the base unit')
                            ->helperText('Automatically reflects the chosen base unit, conversion, or packaging.')
                            ->disabled()
                            ->dehydrated(true),

                        Hidden::make('unit_type')
                            ->default('base'),

                        Hidden::make('unit_reference_id')
                            ->default(null),
                    ])
                    ->columns(4)
                    ->defaultItems(0)
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['inventory_id'] ? (\App\Models\Inventory::find($state['inventory_id'])?->name) : 'Ingredient')
                    ->addActionLabel('Add Ingredient')
                    ->columnSpanFull(),

                Repeater::make('options')
                    ->label('Product Options')
                    ->relationship('options')
                    ->hidden(fn(Get $get) => $get('product_type') !== ProductType::BUNDLE->value)
                    ->helperText('Options are only available for bundle products.')
                    ->schema([
                        TextInput::make('option_name')
                            ->label('Option Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Size, Flavor, Add-ons')
                            ->helperText('Name of the option group'),

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

                        Repeater::make('optionItems')
                            ->label('Option Items')
                            ->relationship('optionItems')
                            ->schema([
                                Select::make('product_id')
                                    ->label('Item Product')
                                    ->relationship('product', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $set('product_packaging_id', null);
                                    }),

                                Select::make('product_packaging_id')
                                    ->label('Product Packaging')
                                    ->searchable()
                                    ->preload()
                                    ->options(function (Get $get) {
                                        $productId = $get('product_id');

                                        if (!$productId) {
                                            return [];
                                        }

                                        return \App\Models\ProductPackaging::where('product_id', $productId)
                                            ->get()
                                            ->mapWithKeys(fn($packaging) => [
                                                $packaging->id => $packaging->name . ' - ' . $packaging->qty . ' ' . $packaging->unit_measure,
                                            ]);
                                    })
                                    ->visible(function (Get $get) {
                                        $productId = $get('product_id');

                                        if (!$productId) {
                                            return false;
                                        }

                                        $product = \App\Models\Product::find($productId);
                                        return $product?->multiple_packaging === true;
                                    })
                                    ->required(function (Get $get) {
                                        $productId = $get('product_id');

                                        if (!$productId) {
                                            return false;
                                        }

                                        $product = \App\Models\Product::find($productId);
                                        return $product?->multiple_packaging === true;
                                    }),

                                TextInput::make('price')
                                    ->label('Additional Price')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->default(0)
                                    ->prefix('₱')
                                    ->helperText('Additional price for this option item'),

                                TextInput::make('quantity')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => \App\Models\Product::find($state['product_id'])?->name ?? 'New Item')
                            ->addActionLabel('Add Option Item')
                            ->defaultItems(0),
                    ])
                    ->columns(1)
                    ->reorderable(false)
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['option_name'] ?? 'New Option')
                    ->addActionLabel('Add Option')
                    ->columnSpanFull()
                    ->defaultItems(0),

                // TextInput::make('total_onhand')
                //     ->required()
                //     ->default(0)
                //     ->numeric()
                //     ->label('Total Onhand'),

                Toggle::make('is_active')
                    ->default(true),

                RichEditor::make('description')
                    ->maxLength(500)
                    ->ColumnSpan(2)
                    ->label('Description'),

                // Select::make('modifiers')
                //     ->relationship('modifiers', 'name')
                //     ->nullable()
                //     ->searchable()
                //     ->multiple()
                //     ->preload(),

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

                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                SpatieMediaLibraryImageColumn::make('featured_image')
                    ->collection('featured_image')
                    ->circular(),

                TextColumn::make('name')
                    ->label('Product Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('barcode')
                    ->label('Barcode')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
            RelationManagers\InventoryRecipesRelationManager::class,
            // RelationManagers\CategoryRelationManager::class,
            // RelationManagers\BrandRelationManager::class,
            RelationManagers\ProductPackagingsRelationManager::class,
            RelationManagers\OptionsRelationManager::class,
            RelationManagers\ProductAddOnsRelationManager::class,
            RelationManagers\RelatedProductsRelationManager::class,
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
