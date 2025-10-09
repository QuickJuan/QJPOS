<?php
namespace App\Filament\Imports;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Group;
use App\Models\Product;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ProductImporter extends Importer
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('uuid')
                ->label('UUID')
                ->fillRecordUsing(fn() => null),

            ImportColumn::make('product_name')
                ->label('Product Name')
                ->fillRecordUsing(fn() => null),

            ImportColumn::make('receipt_name')
                ->label('Receipt Name')
                ->fillRecordUsing(fn() => null),

            ImportColumn::make('product_type')
                ->label('Product Type')
                ->fillRecordUsing(fn() => null),

            ImportColumn::make('description')
                ->label('Description')
                ->fillRecordUsing(fn() => null),

            ImportColumn::make('category')
                ->label('Category')
                ->fillRecordUsing(fn() => null),

            ImportColumn::make('brand')
                ->label('Brand')
                ->fillRecordUsing(fn() => null),
        ];
    }

    public function resolveRecord(): ?Product
    {
        $category = ! empty($this->data['category'])
            ? Category::firstOrCreate(
            ['name' => $this->data['category']],
            ['name' => $this->data['category']]
        )
            : null;

        $brand = ! empty($this->data['brand'])
            ? Brand::firstOrCreate(
            ['name' => $this->data['brand']],
            ['name' => $this->data['brand']]
        )
            : null;

        return Product::firstOrCreate(
            [
                'name'     => $this->data['product_name'],
                'uuid'     => $this->data['uuid'],
                'brand_id' => $brand?->id,
            ],
            [
                'brand_id'      => $brand?->id ?? null,
                'category_id'   => $category?->id ?? null,
                'uuid'          => $this->data['uuid'],
                'name'          => $this->data['product_name'],
                'receipt_alias' => $this->data['receipt_name'],
                'product_type'  => $this->data['product_type'],
                'description'   => $this?->data['description'] ?? null,
            ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your product import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
