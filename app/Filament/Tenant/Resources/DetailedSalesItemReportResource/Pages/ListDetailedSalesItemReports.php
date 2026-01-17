<?php

namespace App\Filament\Tenant\Resources\DetailedSalesItemReportResource\Pages;

use App\Filament\Tenant\Resources\DetailedSalesItemReportResource;
use Filament\Resources\Pages\ListRecords;

class ListDetailedSalesItemReports extends ListRecords
{
    protected static string $resource = DetailedSalesItemReportResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
