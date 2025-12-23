<?php
namespace App\Filament\Tenant\Resources\ProductResource\Pages;

use App\Filament\Tenant\Resources\ProductResource;
use App\Models\Inventory;
use App\Models\ProductInventory;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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
                ->visible(fn () => $this->record->product_type !== 'bundle')
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
                                return;
                            }

                            $inventory = Inventory::find($state);

                            if ($inventory) {
                                $set('unit_measure', $inventory->unit_measure);
                            }
                        }),
                    TextInput::make('quantity')
                        ->label('Quantity Used')
                        ->numeric()
                        ->minValue(0.01)
                        ->step(0.01)
                        ->default(1)
                        ->required(),
                    TextInput::make('unit_measure')
                        ->label('Unit of Measure')
                        ->maxLength(50)
                        ->placeholder('e.g., g, ml, pcs'),
                ])
                ->action(function (array $data) {
                    $product = $this->record;

                    $inventory = Inventory::find($data['inventory_id']);

                    ProductInventory::updateOrCreate(
                        [
                            'product_id'   => $product->id,
                            'inventory_id' => $data['inventory_id'],
                        ],
                        [
                            'quantity'     => (float) $data['quantity'],
                            'unit_measure' => $data['unit_measure']
                                ?: ($inventory?->unit_measure ?? null),
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

                                        $formattedQuantity = rtrim(rtrim(number_format($quantity, 4, '.', ''), '0'), '.');

                                        if ($formattedQuantity === '') {
                                            $formattedQuantity = '0';
                                        }

                                        return trim(sprintf('%s - %s %s', $inventoryName, $formattedQuantity, $unit));
                                    })
                                    ->implode('<br>');

                                return $entries ?: 'No associated inventories';
                            })
                            ->html(),
                    ])
                    ->visible(fn($record) => $record->product_type !== 'bundle'),
            ]);
    }
}
