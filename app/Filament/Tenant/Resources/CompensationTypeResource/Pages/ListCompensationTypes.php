<?php

namespace App\Filament\Tenant\Resources\CompensationTypeResource\Pages;

use App\Filament\Tenant\Resources\CompensationTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCompensationTypes extends ListRecords
{
    protected static string $resource = CompensationTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
