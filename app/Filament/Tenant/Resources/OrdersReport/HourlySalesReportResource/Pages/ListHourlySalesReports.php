<?php

namespace App\Filament\Tenant\Resources\OrdersReport\HourlySalesReportResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Tenant\Resources\OrdersReport\HourlySalesReportResource\HourlySalesReportResource;

class ListHourlySalesReports extends ListRecords
{
    protected static string $resource = HourlySalesReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
