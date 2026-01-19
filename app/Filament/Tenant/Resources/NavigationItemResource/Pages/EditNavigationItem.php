<?php

namespace App\Filament\Tenant\Resources\NavigationItemResource\Pages;

use App\Filament\Tenant\Resources\NavigationItemResource;
use Filament\Resources\Pages\EditRecord;

class EditNavigationItem extends EditRecord
{
    protected static string $resource = NavigationItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
