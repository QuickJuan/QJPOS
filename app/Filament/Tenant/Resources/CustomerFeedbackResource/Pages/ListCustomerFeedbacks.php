<?php

namespace App\Filament\Tenant\Resources\CustomerFeedbackResource\Pages;

use App\Filament\Tenant\Resources\CustomerFeedbackResource;
use Filament\Resources\Pages\ListRecords;

class ListCustomerFeedbacks extends ListRecords
{
    protected static string $resource = CustomerFeedbackResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}

