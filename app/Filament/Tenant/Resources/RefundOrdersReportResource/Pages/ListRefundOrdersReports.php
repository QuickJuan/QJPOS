<?php

namespace App\Filament\Tenant\Resources\RefundOrdersReportResource\Pages;

use App\Filament\Tenant\Resources\RefundOrdersReportResource;
use Filament\Resources\Pages\ListRecords;

class ListRefundOrdersReports extends ListRecords
{
    protected static string $resource = RefundOrdersReportResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
