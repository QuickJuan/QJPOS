<?php

namespace App\Filament\Tenant\Resources\DailySummaryReportResource\Pages;

use App\Filament\Tenant\Resources\DailySummaryReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDailySummaryReports extends ListRecords
{
    protected static string $resource = DailySummaryReportResource::class;

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

                    return route('reports.daily-summary.print', $query);
                })
                ->openUrlInNewTab(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DailySummaryReportResource\Widgets\SalesByCategoryStats::class,
        ];
    }
}
