<?php
namespace App\Filament\Tenant\Resources\LocationResource\Pages;

use App\Filament\Tenant\Resources\LocationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLocation extends CreateRecord
{
    protected static string $resource = LocationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
