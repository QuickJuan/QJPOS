<?php

namespace App\Filament\Tenant\Resources\TableRoomLocationResource\Pages;

use App\Filament\Tenant\Resources\TableRoomLocationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTableRoomLocation extends EditRecord
{
    protected static string $resource = TableRoomLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return route('filament.tenant.resources.table-room-locations.view', $this->record);
    }
}
