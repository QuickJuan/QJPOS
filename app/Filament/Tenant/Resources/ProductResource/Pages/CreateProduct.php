<?php

namespace App\Filament\Tenant\Resources\ProductResource\Pages;

use App\Filament\Tenant\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['multiple_packaging'] = ($data['product_type'] ?? 'simple') === 'with_variant';

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
