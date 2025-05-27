<?php

namespace App\Filament\Tenant\Resources\TableReservationResource\Pages;

use App\Filament\Tenant\Resources\TableReservationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTableReservations extends ListRecords
{
    protected static string $resource = TableReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
