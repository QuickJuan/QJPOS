<?php

namespace App\Filament\Tenant\Resources\ModifierResource\Pages;

use App\Filament\Tenant\Resources\ModifierResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListModifiers extends ListRecords
{
    protected static string $resource = ModifierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
