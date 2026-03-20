<?php

namespace App\Filament\Tenant\Resources\SalesByDateResource\Pages;

use App\Filament\Tenant\Resources\SalesByDateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSalesByDates extends ListRecords
{
    protected static string $resource = SalesByDateResource::class;

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
                        'branch_id' => $filters['branch_id'] ?? null,
                        'from'      => $filters['sale_date']['from'] ?? null,
                        'until'     => $filters['sale_date']['until'] ?? null,
                    ], fn ($value) => filled($value));

                    return route('reports.sales-by-date.print', $query);
                })
                ->openUrlInNewTab(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SalesByDateResource\Widgets\SalesByBranchStats::class,
        ];
    }
}
