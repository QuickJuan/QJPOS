<?php

namespace App\Filament\Tenant\Resources\InventoryResource\Pages;

use App\Filament\Tenant\Resources\InventoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInventories extends ListRecords
{
    protected static string $resource = InventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
