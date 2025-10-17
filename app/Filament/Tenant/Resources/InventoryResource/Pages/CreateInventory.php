<?php
namespace App\Filament\Tenant\Resources\InventoryResource\Pages;

use App\Filament\Tenant\Resources\InventoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInventory extends CreateRecord
{
    protected static string $resource = InventoryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
