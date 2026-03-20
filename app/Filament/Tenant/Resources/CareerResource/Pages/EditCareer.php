<?php

namespace App\Filament\Tenant\Resources\CareerResource\Pages;

use App\Filament\Tenant\Resources\CareerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCareer extends EditRecord
{
    protected static string $resource = CareerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
