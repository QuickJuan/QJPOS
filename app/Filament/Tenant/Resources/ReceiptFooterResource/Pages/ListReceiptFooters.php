<?php

namespace App\Filament\Tenant\Resources\ReceiptFooterResource\Pages;

use App\Filament\Tenant\Resources\ReceiptFooterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReceiptFooters extends ListRecords
{
    protected static string $resource = ReceiptFooterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
