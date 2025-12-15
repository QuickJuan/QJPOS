<?php

namespace App\Filament\Tenant\Resources\OrdersReport\DailySalesReport\PerItemResource\Pages;

use App\Filament\Tenant\Resources\OrdersReport\DailySalesReport\PerItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPerItem extends EditRecord
{
    protected static string $resource = PerItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
