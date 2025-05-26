<?php

namespace App\Filament\Tenant\Resources\ProductOptionResource\Pages;

use App\Filament\Tenant\Resources\ProductOptionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProductOption extends CreateRecord
{
    protected static string $resource = ProductOptionResource::class;
}
