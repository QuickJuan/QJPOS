<?php
namespace App\Filament\Tenant\Resources\InventoryResource\Pages;

use Filament\Actions\EditAction;
use Filament\Infolists\Infolist;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Tenant\Resources\InventoryResource;

class ViewInventory extends ViewRecord
{
    protected static string $resource = InventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make('edit'),

            DeleteAction::make('delete')
                ->successRedirectUrl(route('filament.tenant.resources.inventories.index')),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->columns(2)
            ->schema([
                Section::make('Inventory Details')
                    ->schema([
                        TextEntry::make('name')
                            ->copyable(),

                        TextEntry::make('unitMeasure.name')
                            ->label('Unit Measure'),

                        TextEntry::make('cost'),

                        TextEntry::make('defaultLocation.location')
                            ->label('Default Location'),

                        TextEntry::make('total_current_stock')
                            ->label('Total Current Stock')
                            ->state(fn ($record) => $record->locationStocks->sum('current_stock'))
                            ->formatStateUsing(function ($state, $record) {
                                $unit = $record->unitMeasure?->symbol ?? $record->unit_measure;
                                $formatted = InventoryResource::formatStock($state);

                                return trim($formatted . ' ' . ($unit ?? ''));
                            }),
                    ])
                    ->columns(3)
                    ->columnSpan(2),

                Section::make('Stock by Location')
                    ->schema([
                        RepeatableEntry::make('locationStocks')
                            ->schema([
                                TextEntry::make('location.location')
                                    ->label('Location')
                                    ->placeholder('Unassigned'),

                                TextEntry::make('current_stock')
                                    ->label('Current Stock')
                                    ->formatStateUsing(fn ($state) => InventoryResource::formatStock($state)),
                            ])
                            ->columns(2),
                    ])
                    ->hidden(fn ($record) => $record->locationStocks->isEmpty())
                    ->columnSpan(2),

                Section::make('Unit Conversions')
                    ->hidden(fn ($record) => $record->unitConversions->isEmpty())
                    ->schema([
                        RepeatableEntry::make('unitConversions')
                            ->schema([
                                TextEntry::make('unitMeasure.name')
                                    ->label('Conversion Unit'),

                                TextEntry::make('conversion_factor')
                                    ->label('Base Units per Conversion')
                                    ->formatStateUsing(fn ($state) => $state === null
                                        ? null
                                        : ((float) $state == (int) $state
                                            ? (string) (int) $state
                                            : number_format((float) $state, 2, '.', ''))),
                            ]),
                    ])
                    ->columnSpan(1),

                Section::make('Packagings')
                    ->hidden(fn ($record) => $record->packagings->isEmpty())
                    ->schema([
                        RepeatableEntry::make('packagings')
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Packaging Name'),

                                TextEntry::make('quantity')
                                    ->label('Base Units per Package')
                                    ->formatStateUsing(fn ($state) => $state === null
                                        ? null
                                        : ((float) $state == (int) $state
                                            ? (string) (int) $state
                                            : number_format((float) $state, 2, '.', ''))),
                            ]),
                                    ])
                                    ->columnSpan(1),
            ]);
    }
}
