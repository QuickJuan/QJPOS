<?php

namespace App\Filament\Tenant\Resources\TableRoomLocationResource\Pages;

use App\Filament\Tenant\Resources\TableRoomLocationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTableRoomLocations extends ListRecords
{
    protected static string $resource = TableRoomLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
