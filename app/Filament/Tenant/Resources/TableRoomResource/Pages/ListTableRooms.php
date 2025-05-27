<?php

namespace App\Filament\Tenant\Resources\TableRoomResource\Pages;

use App\Filament\Tenant\Resources\TableRoomResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTableRooms extends ListRecords
{
    protected static string $resource = TableRoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
