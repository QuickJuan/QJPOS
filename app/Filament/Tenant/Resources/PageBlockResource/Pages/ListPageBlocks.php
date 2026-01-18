<?php

namespace App\Filament\Tenant\Resources\PageBlockResource\Pages;

use App\Filament\Tenant\Resources\PageBlockResource;
use Filament\Resources\Pages\ListRecords;

class ListPageBlocks extends ListRecords
{
    protected static string $resource = PageBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
