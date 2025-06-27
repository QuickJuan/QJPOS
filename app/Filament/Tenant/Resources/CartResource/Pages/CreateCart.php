<?php

namespace App\Filament\Tenant\Resources\CartResource\Pages;

use App\Filament\Tenant\Resources\CartResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCart extends CreateRecord
{
    protected static string $resource = CartResource::class;
}
