<?php
namespace App\Filament\Tenant\Resources\ProductPackagingResource\Pages;

use App\Filament\Tenant\Resources\ProductPackagingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductPackaging extends CreateRecord
{
    protected static string $resource = ProductPackagingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
