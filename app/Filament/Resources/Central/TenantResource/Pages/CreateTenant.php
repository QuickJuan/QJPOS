<?php

namespace App\Filament\Resources\Central\TenantResource\Pages;

use App\Filament\Resources\Central\TenantResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;
}
