<?php

namespace App\Filament\Tenant\Resources\DiscountResource\Pages;

use App\Filament\Tenant\Resources\DiscountResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDiscount extends CreateRecord
{
    protected static string $resource = DiscountResource::class;
}
