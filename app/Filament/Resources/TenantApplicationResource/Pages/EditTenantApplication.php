<?php

namespace App\Filament\Resources\TenantApplicationResource\Pages;

use App\Filament\Resources\TenantApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTenantApplication extends EditRecord
{
    protected static string $resource = TenantApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
