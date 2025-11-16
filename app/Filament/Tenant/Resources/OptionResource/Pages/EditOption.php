<?php
namespace App\Filament\Tenant\Resources\OptionResource\Pages;

use App\Filament\Tenant\Resources\OptionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOption extends EditRecord
{
    protected static string $resource = OptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return route('filament.tenant.resources.options.view', $this->record);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['productsPivot'] = $this->record->products->map(function ($product) {
            return [
                'product_id' => $product->id,
                'max_quantity' => $product->pivot->max_quantity ?? 1,
                'is_default' => $product->pivot->is_default ?? false,
            ];
        })->toArray();

        return $data;
    }

    protected function afterSave(): void
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
        } else {
            $this->record->products()->detach();
        }
    }
}
