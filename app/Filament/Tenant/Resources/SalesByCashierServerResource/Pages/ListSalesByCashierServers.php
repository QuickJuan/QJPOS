<?php

namespace App\Filament\Tenant\Resources\SalesByCashierServerResource\Pages;

use App\Filament\Tenant\Resources\SalesByCashierServerResource;
use App\Models\SalesByCashierServer;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSalesByCashierServers extends ListRecords
{
    protected static string $resource = SalesByCashierServerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print')
                ->label('Print Report')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(function () {
                    $filters = $this->getTableFiltersForm()?->getState() ?? [];

                    $query = array_filter([
                        'branch_id'  => $filters['branch_id'] ?? null,
                        'server_id'  => $filters['server_id'] ?? null,
                        'cashier_id' => $filters['cashier_id'] ?? null,
                        'from'       => $filters['sale_date']['from'] ?? null,
                        'until'      => $filters['sale_date']['until'] ?? null,
                    ], fn ($value) => filled($value));

                    return route('reports.sales-by-cashier-server.print', $query);
                })
                ->openUrlInNewTab(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SalesByCashierServerResource\Widgets\SalesByBranchStats::class,
        ];
    }
}
