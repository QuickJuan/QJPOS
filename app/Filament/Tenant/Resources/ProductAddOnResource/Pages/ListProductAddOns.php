<?php

namespace App\Filament\Tenant\Resources\ProductAddOnResource\Pages;

use App\Filament\Tenant\Resources\ProductAddOnResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductAddOns extends ListRecords
{
    protected static string $resource = ProductAddOnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
