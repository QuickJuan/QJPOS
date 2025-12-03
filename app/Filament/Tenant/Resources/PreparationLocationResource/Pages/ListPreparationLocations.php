<?php

namespace App\Filament\Tenant\Resources\PreparationLocationResource\Pages;

use App\Filament\Tenant\Resources\PreparationLocationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPreparationLocations extends ListRecords
{
    protected static string $resource = PreparationLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
