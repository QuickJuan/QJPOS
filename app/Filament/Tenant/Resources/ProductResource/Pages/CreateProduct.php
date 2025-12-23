<?php

namespace App\Filament\Tenant\Resources\ProductResource\Pages;

use App\Filament\Tenant\Resources\ProductResource;
use App\Services\ProductInventorySyncService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['multiple_packaging'] = ($data['product_type'] ?? 'simple') === 'with_variant';
        $data['track_inventory']    = ($data['product_type'] ?? 'simple') === 'simple'
            ? (bool) ($data['track_inventory'] ?? false)
            : false;

        return $data;
    }

    protected function afterCreate(): void
    {
        app(ProductInventorySyncService::class)->ensureInventoryLink($this->record);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
