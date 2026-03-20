<?php

namespace App\Filament\Tenant\Resources\BestSellerReportResource\Pages;

use App\Filament\Tenant\Resources\BestSellerReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBestSellerReports extends ListRecords
{
    protected static string $resource = BestSellerReportResource::class;

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
                        'year_no'   => $filters['year_no'] ?? null,
                        'month_no'  => $filters['month_no'] ?? null,
                    ], fn ($value) => filled($value));

                    return route('reports.best-seller.print', $query);
                })
                ->openUrlInNewTab(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BestSellerReportResource\Widgets\TopProductsStats::class,
        ];
    }
}
