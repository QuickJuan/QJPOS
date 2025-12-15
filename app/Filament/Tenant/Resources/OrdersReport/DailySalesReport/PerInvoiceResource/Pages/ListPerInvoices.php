<?php

namespace App\Filament\Tenant\Resources\OrdersReport\DailySalesReport\PerInvoiceResource\Pages;

use App\Filament\Tenant\Resources\OrdersReport\DailySalesReport\PerInvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPerInvoices extends ListRecords
{
    protected static string $resource = PerInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
