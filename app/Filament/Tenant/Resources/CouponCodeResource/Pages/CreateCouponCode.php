<?php
namespace App\Filament\Tenant\Resources\CouponCodeResource\Pages;

use App\Filament\Tenant\Resources\CouponCodeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCouponCode extends CreateRecord
{
    protected static string $resource = CouponCodeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
