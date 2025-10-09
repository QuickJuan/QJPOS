<?php

namespace App\Filament\Tenant\Resources\InventoryResource\Pages;

use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Tenant\Resources\InventoryResource;

class ViewInventory extends ViewRecord
{
    protected static string $resource = InventoryResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Inventory Details')
                    ->schema([
                        TextEntry::make('name')
                            ->copyable(),

                        TextEntry::make('unit_measure')
                            ->label('Unit Measure'),

                        TextEntry::make('cost'),

                        TextEntry::make('default_location')
                            ->label('Default Location'),
                    ])
                    ->columns(3),
            ]);
    }
}
