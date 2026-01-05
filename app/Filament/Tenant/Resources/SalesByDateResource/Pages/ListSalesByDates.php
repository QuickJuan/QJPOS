<?php

namespace App\Filament\Tenant\Resources\SalesByDateResource\Pages;

use App\Filament\Tenant\Resources\SalesByDateResource;
use Filament\Resources\Pages\ListRecords;

class ListSalesByDates extends ListRecords
{
    protected static string $resource = SalesByDateResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SalesByDateResource\Widgets\SalesByBranchStats::class,
        ];
    }
}
