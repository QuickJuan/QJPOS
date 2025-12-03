<?php

namespace App\Filament\Tenant\Resources\PreparationLocationResource\Pages;

use App\Filament\Tenant\Resources\PreparationLocationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPreparationLocation extends EditRecord
{
    protected static string $resource = PreparationLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
