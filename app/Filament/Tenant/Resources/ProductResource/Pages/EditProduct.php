<?php
namespace App\Filament\Tenant\Resources\ProductResource\Pages;

use App\Filament\Tenant\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (! ($data['product_type'] ?? null)) {
            $data['product_type'] = ($data['multiple_packaging'] ?? false) ? 'with_variant' : 'simple';
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['multiple_packaging'] = ($data['product_type'] ?? 'simple') === 'with_variant';

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return route('filament.tenant.resources.products.view', $this->record);
    }
}
