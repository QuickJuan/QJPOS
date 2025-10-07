<?php

namespace App\Filament\Tenant\Resources\OptionResource\Pages;

use App\Filament\Tenant\Resources\OptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOptions extends ListRecords
{
    protected static string $resource = OptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
