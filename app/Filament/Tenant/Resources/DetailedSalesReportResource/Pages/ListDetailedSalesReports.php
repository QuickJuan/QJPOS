<?php

namespace App\Filament\Tenant\Resources\DetailedSalesReportResource\Pages;

use App\Filament\Tenant\Resources\DetailedSalesReportResource;
use Filament\Resources\Pages\ListRecords;

class ListDetailedSalesReports extends ListRecords
{
    protected static string $resource = DetailedSalesReportResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DetailedSalesReportResource\Widgets\DetailedSalesTotalsStats::class,
        ];
    }

    public function getWidgetData(): array
    {
        $data = parent::getWidgetData();

        $data['tableColumnSearches'] = $data['tableColumnSearches'] ?? [];

        return $data;
    }
}
