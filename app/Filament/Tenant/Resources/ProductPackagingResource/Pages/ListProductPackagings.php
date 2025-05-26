<?php

namespace App\Filament\Tenant\Resources\ProductPackagingResource\Pages;

use App\Filament\Tenant\Resources\ProductPackagingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductPackagings extends ListRecords
{
    protected static string $resource = ProductPackagingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
