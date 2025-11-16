<?php

namespace App\Filament\Resources\TenantApplicationResource\Pages;

use App\Filament\Resources\TenantApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTenantApplication extends CreateRecord
{
    protected static string $resource = TenantApplicationResource::class;
}
