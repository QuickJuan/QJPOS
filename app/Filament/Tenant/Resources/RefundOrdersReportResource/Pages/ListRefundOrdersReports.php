<?php

namespace App\Filament\Tenant\Resources\RefundOrdersReportResource\Pages;

use App\Filament\Tenant\Resources\RefundOrdersReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRefundOrdersReports extends ListRecords
{
    protected static string $resource = RefundOrdersReportResource::class;

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
                        'branch_id'      => $filters['branch_id'] ?? null,
                        'payment_method' => $filters['payment_method'] ?? null,
                        'from'           => $filters['created_at']['from'] ?? null,
                        'until'          => $filters['created_at']['until'] ?? null,
                    ], fn ($value) => filled($value));

                    return route('reports.refund-orders.print', $query);
                })
                ->openUrlInNewTab(),
        ];
    }
}
