<?php

namespace App\Filament\Resources\TenantApplicationResource\Pages;

use App\Filament\Resources\TenantApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTenantApplications extends ListRecords
{
    protected static string $resource = TenantApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
