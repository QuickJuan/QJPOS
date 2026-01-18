<?php

namespace App\Filament\Tenant\Resources\NavigationItemResource\Pages;

use App\Filament\Tenant\Resources\NavigationItemResource;
use Filament\Resources\Pages\ListRecords;

class ListNavigationItems extends ListRecords
{
    protected static string $resource = NavigationItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
