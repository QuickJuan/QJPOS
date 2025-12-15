<?php

namespace App\Filament\Tenant\Resources\OrdersReport\HourlySalesReportResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Tenant\Resources\OrdersReport\HourlySalesReportResource\HourlySalesReportResource;

class EditHourlySalesReport extends EditRecord
{
    protected static string $resource = HourlySalesReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
