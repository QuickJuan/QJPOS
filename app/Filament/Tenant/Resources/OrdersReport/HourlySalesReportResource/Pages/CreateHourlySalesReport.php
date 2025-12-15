<?php

namespace App\Filament\Tenant\Resources\OrdersReport\HourlySalesReportResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Tenant\Resources\OrdersReport\HourlySalesReportResource\HourlySalesReportResource;

class CreateHourlySalesReport extends CreateRecord
{
    protected static string $resource = HourlySalesReportResource::class;
}
