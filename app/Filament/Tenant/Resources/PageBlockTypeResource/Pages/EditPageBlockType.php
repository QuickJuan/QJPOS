<?php

namespace App\Filament\Tenant\Resources\PageBlockTypeResource\Pages;

use App\Filament\Tenant\Resources\PageBlockTypeResource;
use Filament\Resources\Pages\EditRecord;

class EditPageBlockType extends EditRecord
{
    protected static string $resource = PageBlockTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
