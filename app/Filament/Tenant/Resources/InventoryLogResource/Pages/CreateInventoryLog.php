<?php
namespace App\Filament\Tenant\Resources\InventoryLogResource\Pages;

use App\Filament\Tenant\Resources\InventoryLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInventoryLog extends CreateRecord
{
    protected static string $resource = InventoryLogResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
