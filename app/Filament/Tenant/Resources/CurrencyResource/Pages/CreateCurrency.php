<?php

namespace App\Filament\Tenant\Resources\CurrencyResource\Pages;

use App\Filament\Tenant\Resources\CurrencyResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCurrency extends CreateRecord
{
    protected static string $resource = CurrencyResource::class;
}
