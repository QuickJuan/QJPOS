<?php
namespace App\Filament\Tenant\Resources\ProductResource\Pages;

use App\Filament\Tenant\Resources\ProductResource;
use App\Models\Inventory;
use App\Models\ProductInventory;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('associateInventory')
                ->label('Associate Inventory')
                ->icon('heroicon-m-plus-circle')
                ->color('primary')
                ->modalHeading('Associate Inventory')
                ->modalIcon('heroicon-o-archive-box')
                ->modalWidth('lg')
                ->visible(fn () => $this->record->product_type === 'simple' && (bool) $this->record->track_inventory)
                ->form([
                    Select::make('inventory_id')
                        ->label('Inventory Item')
                        ->options(fn () => Inventory::query()->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->reactive()
                        ->required()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if (! $state) {
                                $set('unit_measure', null);
                                $set('use_custom_unit', false);
                                $set('unit_option', null);
                                $set('unit_type', 'base');
                                $set('unit_reference_id', null);
                                return;
                            }

                            $inventory = Inventory::find($state);

                            if ($inventory) {
                                $set('unit_measure', $inventory->unit_measure);
                                $set('use_custom_unit', false);
                                $set('unit_option', null);
                                $set('unit_type', 'base');
                                $set('unit_reference_id', null);
                            }
                        }),

                    TextInput::make('quantity')
                        ->label('Quantity Used')
                        ->numeric()
                        ->minValue(0.01)
                        ->step(0.01)
                        ->default(1)
                        ->required(),

                    Toggle::make('use_custom_unit')
                        ->label('Use Conversion / Packaging')
                        ->helperText('Enable to deduct using a conversion unit or packaging defined on the inventory.')
                        ->hidden(fn (Get $get) => blank($get('inventory_id')))
                        ->dehydrated(false)
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            if (! $state) {
                                $inventoryId = $get('inventory_id');
                                $inventory   = $inventoryId ? Inventory::find($inventoryId) : null;
                                $set('unit_option', null);
                                $set('unit_type', 'base');
                                $set('unit_reference_id', null);
                                $set('unit_measure', $inventory?->unit_measure);
                            }
                        }),

                    Select::make('unit_option')
                        ->label('Select Conversion / Packaging')
                        ->hidden(fn (Get $get) => blank($get('inventory_id')) || ! $get('use_custom_unit'))
                        ->required(fn (Get $get) => (bool) $get('use_custom_unit'))
                        ->dehydrated(false)
                        ->reactive()
                        ->placeholder('Choose an available conversion or packaging')
                        ->options(function (Get $get) {
                            $inventoryId = $get('inventory_id');

                            if (! $inventoryId) {
                                return [];
                            }

                            $inventory = Inventory::with(['unitConversions.unitMeasure', 'packagings'])->find($inventoryId);

                            if (! $inventory) {
                                return [];
                            }

                            $options = [];

                            if ($inventory->unit_measure) {
                                $options['base:0'] = 'Base Unit - ' . $inventory->unit_measure;
                            }

                            foreach ($inventory->unitConversions as $conversion) {
                                $unitName = $conversion->unitMeasure?->name ?? 'Conversion';
                                $factor   = $conversion->conversion_factor ?? 0;
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
                                $qty  = $packaging->quantity ?? 0;
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
                                $set('unit_type', 'base');
                                $set('unit_reference_id', null);
                                $set('unit_measure', null);
                                return;
                            }

                            $inventory = Inventory::with(['unitConversions.unitMeasure', 'packagings'])->find($inventoryId);

                            if (! $inventory) {
                                $set('unit_type', 'base');
                                $set('unit_reference_id', null);
                                $set('unit_measure', null);
                                return;
                            }

                            if (! $state || $state === 'base:0') {
                                $set('unit_type', 'base');
                                $set('unit_reference_id', null);
                                $set('unit_measure', $inventory->unit_measure);
                                return;
                            }

                            [$type, $id] = array_pad(explode(':', $state), 2, null);
                            $id = $id ? (int) $id : null;

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
                        ->label('Unit of Measure')
                        ->maxLength(50)
                        ->disabled()
                        ->placeholder('Auto-filled from the selected unit')
                        ->helperText('Reflects the base unit, conversion, or packaging that will be deducted.')
                        ->dehydrated(true),

                    Hidden::make('unit_type')
                        ->default('base'),

                    Hidden::make('unit_reference_id')
                        ->default(null),
                ])
                ->action(function (array $data) {
                    $product = $this->record;

                    $inventory = Inventory::find($data['inventory_id']);
                    $unitType = $data['unit_type'] ?? 'base';
                    $unitReferenceId = $unitType === 'base'
                        ? null
                        : ($data['unit_reference_id'] ?? null);
                    $unitMeasure = ($data['unit_measure'] ?? null)
                        ?: ($inventory?->unit_measure ?? null);

                    ProductInventory::updateOrCreate(
                        [
                            'product_id'   => $product->id,
                            'inventory_id' => $data['inventory_id'],
                        ],
                        [
                            'quantity'     => (float) $data['quantity'],
                            'unit_measure' => $unitMeasure,
                            'unit_type'    => $unitType,
                            'unit_reference_id' => $unitReferenceId,
                        ],
                    );

                    $product->refresh();

                    Notification::make()
                        ->title('Inventory Associated')
                        ->body('Inventory item has been linked to this product.')
                        ->success()
                        ->send();
                }),

            EditAction::make('edit'),

            DeleteAction::make('delete')
                ->successRedirectUrl(route('filament.tenant.resources.products.index')),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Product Details')
                    ->schema([
                        TextEntry::make('category.name'),

                        TextEntry::make('brand.name'),

                        TextEntry::make('preparationLocation.description'),

                        TextEntry::make('track_inventory')
                            ->label('Tracks Inventory')
                            ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No'),

                        TextEntry::make('uuid')
                            ->label('UUID'),

                        TextEntry::make('name')
                            ->label('Product Name'),

                        TextEntry::make('receipt_alias')
                            ->label('Receipt Name'),

                        TextEntry::make('description')
                            ->label('Description')
                            ->html(),
                    ])
                    ->columns(4),

                Section::make('Tax Information')
                    ->schema([
                        TextEntry::make('vat_type')
                            ->label('Tax Type')
                            ->formatStateUsing(fn($state) => \App\Enums\VatType::tryFrom($state)?->getLabel() ?? '-'),

                        TextEntry::make('vat_inclusive')
                            ->label('Tax Inclusive')
                            ->formatStateUsing(fn($state) => $state ? 'Yes' : 'No')
                            ->visible(fn($record) => $record->vat_type === \App\Enums\VatType::VAT->value),

                        TextEntry::make('vat_rate')
                            ->label('Tax Rate')
                            ->formatStateUsing(fn($state) => $state ? $state . '%' : '-')
                            ->visible(fn($record) => $record->vat_type === \App\Enums\VatType::VAT->value),
                    ])
                    ->columns(3),

                Section::make('Images')
                    ->schema([
                        ImageEntry::make('featured_image')
                            ->label('Featured Image')
                            ->getStateUsing(fn($record) => $record->getFirstMediaUrl('featured_image'))
                            ->square(),

                        ImageEntry::make('product_images')
                            ->label('Product Images')
                            ->getStateUsing(fn($record) => $record->getFirstMediaUrl('product_images'))
                            ->square(),
                    ]),

                Section::make('Associated Groups')
                    ->schema([
                        TextEntry::make('groups')
                            ->label('Groups')
                            ->formatStateUsing(fn($record) =>
                                $record->groups
                                    ->unique('id')
                                    ->pluck('name')
                                    ->implode('<br>')
                            )
                            ->html(),
                    ]),

                Section::make('Associated Options')
                    ->schema([
                        TextEntry::make('options')
                            ->label('Options')
                            ->formatStateUsing(fn($record) =>
                                $record->options
                                    ->unique('id')
                                    ->pluck('option_name')
                                    ->implode('<br>')
                            )
                            ->html(),
                    ])
                    ->visible(fn($record) => $record->product_type === 'bundle'),

                Section::make('Associated Inventories')
                    ->schema([
                        TextEntry::make('inventoryRecipes')
                            ->label('Inventories')
                            ->formatStateUsing(function ($record) {
                                $record->loadMissing('inventoryRecipes.inventory');

                                $entries = $record->inventoryRecipes
                                    ->filter(fn($recipe) => $recipe->inventory)
                                    ->map(function ($recipe) {
                                        $inventoryName = $recipe->inventory->name;
                                        $quantity = $recipe->quantity ?? 0;
                                        $unit = $recipe->unit_measure
                                            ?? $recipe->inventory->unit_measure
                                            ?? '';

                                        $typeLabel = match ($recipe->unit_type) {
                                            'conversion' => ' (Conversion)',
                                            'packaging'  => ' (Packaging)',
                                            default       => '',
                                        };

                                        $formattedQuantity = rtrim(rtrim(number_format($quantity, 4, '.', ''), '0'), '.');

                                        if ($formattedQuantity === '') {
                                            $formattedQuantity = '0';
                                        }

                                        return trim(sprintf('%s - %s %s%s', $inventoryName, $formattedQuantity, $unit, $typeLabel));
                                    })
                                    ->implode('<br>');

                                return $entries ?: 'No associated inventories';
                            })
                            ->html(),
                    ])
                    ->visible(function ($record) {
                        if ($record->product_type === 'simple') {
                            return (bool) $record->track_inventory;
                        }

                        return $record->product_type !== 'bundle';
                    }),
            ]);
    }
}
