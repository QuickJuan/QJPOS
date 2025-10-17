<?php
namespace App\Filament\Tenant\Resources\TableReservationResource\Pages;

use App\Filament\Tenant\Resources\TableReservationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTableReservation extends CreateRecord
{
    protected static string $resource = TableReservationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
