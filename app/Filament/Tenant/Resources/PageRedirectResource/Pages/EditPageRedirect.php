<?php

namespace App\Filament\Tenant\Resources\PageRedirectResource\Pages;

use App\Filament\Tenant\Resources\PageRedirectResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPageRedirect extends EditRecord
{
    protected static string $resource = PageRedirectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
