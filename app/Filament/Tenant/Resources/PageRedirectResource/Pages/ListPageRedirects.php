<?php

namespace App\Filament\Tenant\Resources\PageRedirectResource\Pages;

use App\Filament\Tenant\Resources\PageRedirectResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPageRedirects extends ListRecords
{
    protected static string $resource = PageRedirectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
