<?php

namespace App\Filament\Tenant\Resources\PageSEOResource\Pages;

use App\Filament\Tenant\Resources\PageSEOResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPageSEO extends EditRecord
{
    protected static string $resource = PageSEOResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
