<?php
namespace App\Filament\Tenant\Resources\OrdersReport\BestSellerReportResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use App\Filament\Tenant\Resources\OrdersReport\BestSellerReportResource\BestSellerReportResource;

class CreateBestSellerReport extends CreateRecord
{
    protected static string $resource = BestSellerReportResource::class;
}
