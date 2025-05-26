<?php

namespace App\Filament\Tenant\Resources\ProductOptionResource\Pages;

use App\Filament\Tenant\Resources\ProductOptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductOptions extends ListRecords
{
    protected static string $resource = ProductOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
