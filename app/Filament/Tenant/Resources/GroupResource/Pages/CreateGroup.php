<?php
namespace App\Filament\Tenant\Resources\GroupResource\Pages;

use App\Filament\Tenant\Resources\GroupResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGroup extends CreateRecord
{
    protected static string $resource = GroupResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
