<?php

namespace App\Filament\Tenant\Resources\OrdersReport\DailySalesReport\PerInvoiceResource\Pages;

use App\Filament\Tenant\Resources\OrdersReport\DailySalesReport\PerInvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePerInvoice extends CreateRecord
{
    protected static string $resource = PerInvoiceResource::class;
}
