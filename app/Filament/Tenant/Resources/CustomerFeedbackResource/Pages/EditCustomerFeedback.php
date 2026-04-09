<?php

namespace App\Filament\Tenant\Resources\CustomerFeedbackResource\Pages;

use App\Filament\Tenant\Resources\CustomerFeedbackResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomerFeedback extends EditRecord
{
    protected static string $resource = CustomerFeedbackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

