<?php
namespace App\Filament\Tenant\Resources\InventoryLogResource\Pages;

use App\Filament\Tenant\Resources\InventoryLogResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewInventoryLog extends ViewRecord
{
    protected static string $resource = InventoryLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make('edit'),

            DeleteAction::make('delete')
                ->successRedirectUrl(route('filament.tenant.resources.inventory-logs.index')),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Inventory Log Details')
                    ->schema([
                        TextEntry::make('inventoryLocation.location'),
                        
                        TextEntry::make('adjustment'),

                        TextEntry::make('new_balance'),

                        TextEntry::make('user.name'),

                        TextEntry::make('approved_by')
                            ->label('Approved By'),
                    ])
                    ->columns(3),
            ]);
    }
}
