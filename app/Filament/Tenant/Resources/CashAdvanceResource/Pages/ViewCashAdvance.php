<?php

namespace App\Filament\Tenant\Resources\CashAdvanceResource\Pages;

use App\Filament\Tenant\Resources\CashAdvanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCashAdvance extends ViewRecord
{
    protected static string $resource = CashAdvanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
