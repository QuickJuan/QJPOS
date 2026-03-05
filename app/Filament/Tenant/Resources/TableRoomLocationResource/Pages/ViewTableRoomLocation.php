<?php
namespace App\Filament\Tenant\Resources\TableRoomLocationResource\Pages;

use Filament\Actions\EditAction;
use Filament\Infolists\Infolist;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Tenant\Resources\TableRoomLocationResource;
use App\Enums\TableRoomLocation\ServiceChargeType;

class ViewTableRoomLocation extends ViewRecord
{
    protected static string $resource = TableRoomLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make('edit'),

            DeleteAction::make('delete')
                ->successRedirectUrl(route('filament.tenant.resources.table-room-locations.index')),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Table Room Location Details')
                    ->schema([
                        TextEntry::make('name'),

                        TextEntry::make('service_charge_label')
                            ->label('Service Charge Label')
                            ->state(fn ($record) => $record->service_charge_label ?? 'Service Charge'),

                        TextEntry::make('service_charge')
                            ->label('Service Charge (%)'),

                        TextEntry::make('service_charge_type')
                            ->label('Service Charge Type')
                            ->formatStateUsing(fn ($state) => ucfirst($state ?? ServiceChargeType::AUTO->value)),
                    ])
                    ->columns(3),
            ]);
    }
}
