<?php

namespace App\Filament\Tenant\Resources\OrdersReport\BestSellerReportResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Tenant\Resources\OrdersReport\BestSellerReportResource\BestSellerReportResource;

class EditBestSellerReport extends EditRecord
{
    protected static string $resource = BestSellerReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
