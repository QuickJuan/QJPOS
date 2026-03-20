<?php

namespace App\Filament\Tenant\Resources\CompensationGroupResource\Pages;

use App\Filament\Tenant\Resources\CompensationGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCompensationGroups extends ListRecords
{
    protected static string $resource = CompensationGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
