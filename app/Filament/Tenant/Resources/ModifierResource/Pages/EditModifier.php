<?php

namespace App\Filament\Tenant\Resources\ModifierResource\Pages;

use App\Filament\Tenant\Resources\ModifierResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditModifier extends EditRecord
{
    protected static string $resource = ModifierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
