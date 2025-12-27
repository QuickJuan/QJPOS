<?php

namespace App\Filament\Tenant\Resources\CurrencyDenominationResource\Pages;

use App\Filament\Tenant\Resources\CurrencyDenominationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCurrencyDenominations extends ListRecords
{
    protected static string $resource = CurrencyDenominationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
