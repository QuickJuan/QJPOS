<?php
namespace App\Filament\Tenant\Resources\OptionResource\Pages;

use App\Filament\Tenant\Resources\OptionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOption extends CreateRecord
{
    protected static string $resource = OptionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
