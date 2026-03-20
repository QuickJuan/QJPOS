<?php

namespace App\Filament\Tenant\Resources\DetailedSalesItemReportResource\Pages;

use App\Filament\Tenant\Resources\DetailedSalesItemReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDetailedSalesItemReports extends ListRecords
{
    protected static string $resource = DetailedSalesItemReportResource::class;

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
                        'status'    => $filters['status'] ?? null,
                        'from'      => $filters['order_created_at']['from'] ?? null,
                        'until'     => $filters['order_created_at']['until'] ?? null,
                    ], fn ($value) => filled($value));

                    return route('reports.detailed-sales-item.print', $query);
                })
                ->openUrlInNewTab(),
        ];
    }
}
