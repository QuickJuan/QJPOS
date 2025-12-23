<?php

namespace App\Filament\Tenant\Resources\InventoryStockMovementResource\Pages;

use App\Filament\Tenant\Resources\InventoryStockMovementResource;
use App\Services\InventoryStockService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateInventoryStockMovement extends CreateRecord
{
    protected static string $resource = InventoryStockMovementResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(InventoryStockService::class)->adjust($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
