<?php
namespace App\Filament\Tenant\Resources\TableReservationResource\Pages;

use App\Filament\Tenant\Resources\TableReservationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTableReservation extends EditRecord
{
    protected static string $resource = TableReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return route('filament.tenant.resources.table-reservations.view', $this->record);
    }
}
