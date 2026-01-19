<?php

namespace App\Filament\Tenant\Resources\PageSEOResource\Pages;

use App\Filament\Tenant\Resources\PageSEOResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePageSEO extends CreateRecord
{
    protected static string $resource = PageSEOResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
