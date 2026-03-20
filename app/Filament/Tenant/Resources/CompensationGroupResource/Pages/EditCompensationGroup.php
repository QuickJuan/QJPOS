<?php

namespace App\Filament\Tenant\Resources\CompensationGroupResource\Pages;

use App\Filament\Tenant\Resources\CompensationGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompensationGroup extends EditRecord
{
    protected static string $resource = CompensationGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
