<?php

namespace App\Filament\Tenant\Resources\DetailedSalesReportResource\Pages;

use App\Filament\Tenant\Resources\DetailedSalesReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDetailedSalesReports extends ListRecords
{
    protected static string $resource = DetailedSalesReportResource::class;

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
                        'cashier_id' => $filters['cashier_id'] ?? null,
                        'status'     => $filters['status'] ?? null,
                        'from'       => $filters['created_at']['from'] ?? null,
                        'until'      => $filters['created_at']['until'] ?? null,
                    ], fn ($value) => filled($value));

                    return route('reports.detailed-sales.print', $query);
                })
                ->openUrlInNewTab(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DetailedSalesReportResource\Widgets\DetailedSalesTotalsStats::class,
        ];
    }

    public function getWidgetData(): array
    {
        $data = parent::getWidgetData();

        $data['tableColumnSearches'] = $data['tableColumnSearches'] ?? [];

        return $data;
    }
}
