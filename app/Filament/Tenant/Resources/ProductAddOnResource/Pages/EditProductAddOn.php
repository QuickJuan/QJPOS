<?php

namespace App\Filament\Tenant\Resources\ProductAddOnResource\Pages;

use App\Filament\Tenant\Resources\ProductAddOnResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductAddOn extends EditRecord
{
    protected static string $resource = ProductAddOnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
