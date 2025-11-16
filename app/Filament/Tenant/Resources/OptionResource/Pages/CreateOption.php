<?php
namespace App\Filament\Tenant\Resources\OptionResource\Pages;

use App\Filament\Tenant\Resources\OptionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOption extends CreateRecord
{
    protected static string $resource = OptionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $productsPivot = $this->data['productsPivot'] ?? [];

        if (!empty($productsPivot)) {
            $syncData = [];
            foreach ($productsPivot as $pivot) {
                $syncData[$pivot['product_id']] = [
                    'max_quantity' => $pivot['max_quantity'] ?? 1,
                    'is_default' => $pivot['is_default'] ?? false,
                ];
            }
            $this->record->products()->sync($syncData);
        }
    }
}
