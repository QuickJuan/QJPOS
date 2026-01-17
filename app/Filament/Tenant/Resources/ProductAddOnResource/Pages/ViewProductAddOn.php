<?php

namespace App\Filament\Tenant\Resources\ProductAddOnResource\Pages;

use App\Filament\Tenant\Resources\ProductAddOnResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProductAddOn extends ViewRecord
{
    protected static string $resource = ProductAddOnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
