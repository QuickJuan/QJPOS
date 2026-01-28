<?php

namespace App\Filament\Tenant\Resources\SalesInvoiceReportResource\Pages;

use App\Filament\Tenant\Resources\SalesInvoiceReportResource;
use Filament\Resources\Pages\ListRecords;

class ListSalesInvoiceReports extends ListRecords
{
    protected static string $resource = SalesInvoiceReportResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
