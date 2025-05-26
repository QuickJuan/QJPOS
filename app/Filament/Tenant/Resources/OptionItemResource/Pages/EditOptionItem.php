<?php

namespace App\Filament\Tenant\Resources\OptionItemResource\Pages;

use App\Filament\Tenant\Resources\OptionItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOptionItem extends EditRecord
{
    protected static string $resource = OptionItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
