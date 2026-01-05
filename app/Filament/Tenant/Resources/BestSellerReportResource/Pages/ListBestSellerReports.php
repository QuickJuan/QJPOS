<?php

namespace App\Filament\Tenant\Resources\BestSellerReportResource\Pages;

use App\Filament\Tenant\Resources\BestSellerReportResource;
use Filament\Resources\Pages\ListRecords;

class ListBestSellerReports extends ListRecords
{
    protected static string $resource = BestSellerReportResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BestSellerReportResource\Widgets\TopProductsStats::class,
        ];
    }
}
