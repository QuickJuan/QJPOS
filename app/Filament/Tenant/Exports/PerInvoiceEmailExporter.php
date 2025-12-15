<?php
namespace App\Filament\Tenant\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use Illuminate\Database\Eloquent\Builder;

class PerInvoiceEmailExporter implements FromQuery, WithHeadings, WithCustomStartCell, WithEvents
{
    protected Builder $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    public function query(): Builder
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'Order Date',
            'Cashier Shift Number',
            'Customer',
            'Invoice Number',
            'Gross Sales',
            'Discount',
            'Net Sales',
            'Status',
        ];
    }

    public function startCell(): string
    {
        return "A1";
    }

    public function registerEvents(): array
    {
        $records = $this->query->get();

        $grand_total = $records->sum(fn($r) => $r->net_sales);

        return [
            BeforeSheet::class => function (BeforeSheet $event) use ($grand_total): void {
                $sheet = $event->sheet->getDelegate();
                $sheet->setCellValue('A1', "");
            },
            AfterSheet::class  => function (AfterSheet $event) use ($grand_total): void {
                $sheet    = $event->sheet->getDelegate();
                $lastRow  = $sheet->getHighestRow();
                $totalRow = $lastRow + 2;

                $sheet->setCellValue('F' . $totalRow, 'Grand Total:');
                $sheet->setCellValue('G' . $totalRow, $grand_total);
            },
        ];
    }
}
