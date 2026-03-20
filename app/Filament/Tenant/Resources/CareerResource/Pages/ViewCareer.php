<?php

namespace App\Filament\Tenant\Resources\CareerResource\Pages;

use App\Filament\Tenant\Resources\CareerResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCareer extends ViewRecord
{
    protected static string $resource = CareerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
