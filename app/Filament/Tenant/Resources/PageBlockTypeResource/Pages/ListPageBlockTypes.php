<?php

namespace App\Filament\Tenant\Resources\PageBlockTypeResource\Pages;

use App\Filament\Tenant\Resources\PageBlockTypeResource;
use Filament\Resources\Pages\ListRecords;

class ListPageBlockTypes extends ListRecords
{
    protected static string $resource = PageBlockTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
