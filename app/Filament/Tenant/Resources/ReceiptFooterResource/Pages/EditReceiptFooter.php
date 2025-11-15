<?php

namespace App\Filament\Tenant\Resources\ReceiptFooterResource\Pages;

use App\Filament\Tenant\Resources\ReceiptFooterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReceiptFooter extends EditRecord
{
    protected static string $resource = ReceiptFooterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
