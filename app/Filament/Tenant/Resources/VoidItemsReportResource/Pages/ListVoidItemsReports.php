<?php

namespace App\Filament\Tenant\Resources\VoidItemsReportResource\Pages;

use App\Filament\Tenant\Resources\VoidItemsReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVoidItemsReports extends ListRecords
{
    protected static string $resource = VoidItemsReportResource::class;

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
                        'cashier_id' => $filters['cashier_id'] ?? null,
                        'from'       => $filters['voided_at']['from'] ?? null,
                        'until'      => $filters['voided_at']['until'] ?? null,
                    ], fn ($value) => filled($value));

                    return route('reports.void-items.print', $query);
                })
                ->openUrlInNewTab(),
        ];
    }
}
