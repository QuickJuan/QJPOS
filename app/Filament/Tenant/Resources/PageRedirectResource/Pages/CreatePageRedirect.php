<?php

namespace App\Filament\Tenant\Resources\PageRedirectResource\Pages;

use App\Filament\Tenant\Resources\PageRedirectResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePageRedirect extends CreateRecord
{
    protected static string $resource = PageRedirectResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
