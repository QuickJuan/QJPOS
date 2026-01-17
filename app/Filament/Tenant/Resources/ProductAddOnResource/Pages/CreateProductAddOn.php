<?php

namespace App\Filament\Tenant\Resources\ProductAddOnResource\Pages;

use App\Filament\Tenant\Resources\ProductAddOnResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductAddOn extends CreateRecord
{
    protected static string $resource = ProductAddOnResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
