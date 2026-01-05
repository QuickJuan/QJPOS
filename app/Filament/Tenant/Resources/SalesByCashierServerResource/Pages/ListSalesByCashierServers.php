<?php

namespace App\Filament\Tenant\Resources\SalesByCashierServerResource\Pages;

use App\Filament\Tenant\Resources\SalesByCashierServerResource;
use App\Models\SalesByCashierServer;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSalesByCashierServers extends ListRecords
{
    protected static string $resource = SalesByCashierServerResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SalesByCashierServerResource\Widgets\SalesByBranchStats::class,
        ];
    }
}
