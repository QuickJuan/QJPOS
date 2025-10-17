<?php
namespace App\Filament\Tenant\Resources\CouponCodeResource\Pages;

use App\Filament\Tenant\Resources\CouponCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCouponCode extends EditRecord
{
    protected static string $resource = CouponCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return route('filament.tenant.resources.coupon-codes.view', $this->record);
    }
}
