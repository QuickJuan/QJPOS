<?php

namespace App\Filament\Tenant\Resources\ProductResource\Pages;

use App\Filament\Tenant\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $optionsPivot = $this->data['optionsPivot'] ?? [];

        if (!empty($optionsPivot)) {
            $syncData = [];
            foreach ($optionsPivot as $pivot) {
                $syncData[$pivot['option_id']] = [
                    'max_quantity' => $pivot['max_quantity'] ?? 1,
                    'is_default' => $pivot['is_default'] ?? false,
                ];
            }
            $this->record->options()->sync($syncData);
        }
    }
}
