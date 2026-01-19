<?php

namespace App\Filament\Tenant\Resources\PageSEOResource\Pages;

use App\Filament\Tenant\Resources\PageSEOResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPageSEOS extends ListRecords
{
    protected static string $resource = PageSEOResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
