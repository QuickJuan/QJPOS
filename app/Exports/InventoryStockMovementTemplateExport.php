<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class InventoryStockMovementTemplateExport implements FromArray, WithHeadings, WithTitle
{
    public function headings(): array
    {
        return [
            'inventory',
            'location',
            'movement_type',
            'quantity',
            'notes',
        ];
    }

    public function array(): array
    {
        return [
            ['Sample Inventory Item', 'Main Kitchen', 'in', 25, 'Optional note'],
        ];
    }

    public function title(): string
    {
        return 'Stock Movements';
    }
}
