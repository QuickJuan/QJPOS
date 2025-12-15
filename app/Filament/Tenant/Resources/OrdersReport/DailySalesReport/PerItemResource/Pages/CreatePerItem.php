<?php

namespace App\Filament\Tenant\Resources\OrdersReport\DailySalesReport\PerItemResource\Pages;

use App\Filament\Tenant\Resources\OrdersReport\DailySalesReport\PerItemResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePerItem extends CreateRecord
{
    protected static string $resource = PerItemResource::class;
}
