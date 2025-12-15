<?php

namespace App\Filament\Tenant\Resources\OrdersReport\BestSellerReportResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Tenant\Resources\OrdersReport\BestSellerReportResource\BestSellerReportResource;

class ListBestSellerReports extends ListRecords
{
    protected static string $resource = BestSellerReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
