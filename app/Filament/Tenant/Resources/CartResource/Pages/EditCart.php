<?php
namespace App\Filament\Tenant\Resources\CartResource\Pages;

use App\Filament\Tenant\Resources\CartResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCart extends EditRecord
{
    protected static string $resource = CartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return route('filament.tenant.resources.carts.view', $this->record);
    }
}
