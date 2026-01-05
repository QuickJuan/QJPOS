<?php

namespace App\Filament\Tenant\Resources\CashierSessionResource\Pages;

use App\Filament\Tenant\Resources\CashierSessionResource;
use Filament\Resources\Pages\ListRecords;

class ListCashierSessions extends ListRecords
{
    protected static string $resource = CashierSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
