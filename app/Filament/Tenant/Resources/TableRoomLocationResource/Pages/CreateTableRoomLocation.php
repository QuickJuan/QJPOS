<?php
namespace App\Filament\Tenant\Resources\TableRoomLocationResource\Pages;

use App\Filament\Tenant\Resources\TableRoomLocationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTableRoomLocation extends CreateRecord
{
    protected static string $resource = TableRoomLocationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
