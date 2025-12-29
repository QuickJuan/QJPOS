<?php

namespace App\Filament\Tenant\Resources\DailySummaryReportResource\Pages;

use App\Filament\Tenant\Resources\DailySummaryReportResource;
use Filament\Resources\Pages\ListRecords;

class ListDailySummaryReports extends ListRecords
{
    protected static string $resource = DailySummaryReportResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DailySummaryReportResource\Widgets\SalesByCategoryStats::class,
        ];
    }
}
