<?php

namespace App\Filament\Tenant\Resources\CashDrawerRequestResource\Pages;

use App\Filament\Tenant\Resources\CashDrawerRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListCashDrawerRequests extends ListRecords
{
    protected static string $resource = CashDrawerRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
