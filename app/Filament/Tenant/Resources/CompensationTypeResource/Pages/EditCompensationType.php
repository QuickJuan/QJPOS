<?php

namespace App\Filament\Tenant\Resources\CompensationTypeResource\Pages;

use App\Filament\Tenant\Resources\CompensationTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompensationType extends EditRecord
{
    protected static string $resource = CompensationTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
