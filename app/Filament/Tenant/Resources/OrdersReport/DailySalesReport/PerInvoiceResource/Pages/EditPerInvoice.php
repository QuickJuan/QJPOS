<?php

namespace App\Filament\Tenant\Resources\OrdersReport\DailySalesReport\PerInvoiceResource\Pages;

use App\Filament\Tenant\Resources\OrdersReport\DailySalesReport\PerInvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPerInvoice extends EditRecord
{
    protected static string $resource = PerInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
