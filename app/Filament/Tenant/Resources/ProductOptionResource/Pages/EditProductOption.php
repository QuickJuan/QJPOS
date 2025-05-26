<?php

namespace App\Filament\Tenant\Resources\ProductOptionResource\Pages;

use App\Filament\Tenant\Resources\ProductOptionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductOption extends EditRecord
{
    protected static string $resource = ProductOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
