<?php
namespace App\Filament\Tenant\Exports\HourlySalesReport;

use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class HourlySalesReportExporter extends ExcelExport implements WithCustomStartCell, WithEvents
{
    public function startCell(): string
    {
        return "A4";
    }

    public function registerEvents(): array
    {
        $records = $this->getQuery()->get();

        $grand_total = $records->sum(fn($r) => $r->net_sales);

        return [
            BeforeSheet::class => function (BeforeSheet $event) use ($grand_total): void {
                $sheet = $event->sheet->getDelegate();
                $sheet->setCellValue('A5', "");
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
