<?php

namespace App\Filament\Tenant\Resources\ScheduleTypeResource\Pages;

use App\Filament\Tenant\Resources\ScheduleTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListScheduleTypes extends ListRecords
{
    protected static string $resource = ScheduleTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
