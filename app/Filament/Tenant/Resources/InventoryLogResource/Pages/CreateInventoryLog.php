<?php

namespace App\Filament\Tenant\Resources\InventoryLogResource\Pages;

use App\Filament\Tenant\Resources\InventoryLogResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInventoryLog extends CreateRecord
{
    protected static string $resource = InventoryLogResource::class;
}
