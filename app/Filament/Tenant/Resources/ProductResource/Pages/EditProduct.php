<?php
namespace App\Filament\Tenant\Resources\ProductResource\Pages;

use App\Filament\Tenant\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['optionsPivot'] = $this->record->options->map(function ($option) {
            return [
                'option_id' => $option->id,
                'max_quantity' => $option->pivot->max_quantity ?? 1,
                'is_default' => $option->pivot->is_default ?? false,
            ];
        })->toArray();

        return $data;
    }

    protected function afterSave(): void
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
        } else {
            $this->record->options()->detach();
        }
    }
}
