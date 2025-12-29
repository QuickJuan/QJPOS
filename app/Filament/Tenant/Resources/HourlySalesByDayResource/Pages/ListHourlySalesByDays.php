<?php

namespace App\Filament\Tenant\Resources\HourlySalesByDayResource\Pages;

use App\Filament\Tenant\Resources\HourlySalesByDayResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHourlySalesByDays extends ListRecords
{
    protected static string $resource = HourlySalesByDayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            HourlySalesByDayResource\Widgets\HourlySalesStats::class,
        ];
    }
}
