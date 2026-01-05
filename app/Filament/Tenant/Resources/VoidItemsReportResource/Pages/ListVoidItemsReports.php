<?php

namespace App\Filament\Tenant\Resources\VoidItemsReportResource\Pages;

use App\Filament\Tenant\Resources\VoidItemsReportResource;
use Filament\Resources\Pages\ListRecords;

class ListVoidItemsReports extends ListRecords
{
    protected static string $resource = VoidItemsReportResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
