<?php
namespace App\Filament\Tenant\Resources\TableRoomResource\Pages;

use App\Filament\Tenant\Resources\TableRoomResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTableRoom extends EditRecord
{
    protected static string $resource = TableRoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return route('filament.tenant.resources.table-rooms.view', $this->record);
    }
}
