<?php

namespace App\Filament\Tenant\Resources\CashAdvanceResource\Pages;

use App\Filament\Tenant\Resources\CashAdvanceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCashAdvance extends EditRecord
{
    protected static string $resource = CashAdvanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
