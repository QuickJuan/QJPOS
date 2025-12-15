<?php

namespace App\Filament\Tenant\Resources\OrdersReport\DailySalesReport\PerItemResource\Pages;

use App\Filament\Tenant\Resources\OrdersReport\DailySalesReport\PerItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPerItems extends ListRecords
{
    protected static string $resource = PerItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
