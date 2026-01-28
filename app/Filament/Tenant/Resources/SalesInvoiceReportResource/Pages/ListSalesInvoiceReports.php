<?php

namespace App\Filament\Tenant\Resources\SalesInvoiceReportResource\Pages;

use App\Filament\Tenant\Resources\SalesInvoiceReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSalesInvoiceReports extends ListRecords
{
    protected static string $resource = SalesInvoiceReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print')
                ->label('Print')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(function () {
                    $filters = $this->getTableFiltersForm()?->getState() ?? [];

                    $query = array_filter([
                        'branch_id' => $filters['branch_id'] ?? null,
                        'cashier_id' => $filters['cashier_id'] ?? null,
                        'cashier_session_id' => $filters['cashier_session_id'] ?? null,
                        'status' => $filters['status'] ?? null,
                        'from' => $filters['created_at']['from'] ?? null,
                        'until' => $filters['created_at']['until'] ?? null,
                    ], fn ($value) => filled($value));

                    return route('sales-invoice-report.print', $query);
                })
                ->openUrlInNewTab(),
        ];
    }
}
