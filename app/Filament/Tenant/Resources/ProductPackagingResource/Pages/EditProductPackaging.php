<?php

namespace App\Filament\Tenant\Resources\ProductPackagingResource\Pages;

use App\Filament\Tenant\Resources\ProductPackagingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductPackaging extends EditRecord
{
    protected static string $resource = ProductPackagingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
