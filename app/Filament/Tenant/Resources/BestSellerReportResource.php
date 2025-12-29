<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\BestSellerReportResource\Pages;
use App\Filament\Tenant\Exports\BestSellerReport\BestSellerReportExporter;
use App\Models\BestSellerReport;
use App\Models\Branch;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;

class BestSellerReportResource extends Resource
{
    protected static ?string $model = BestSellerReport::class;
    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    protected static ?string $navigationLabel = 'Best Seller Report';
    protected static ?string $navigationGroup = 'Sales Report';
    protected static ?int $navigationSort = 11;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable()
                    ->sortable()
                    ->default('N/A'),
                Tables\Columns\TextColumn::make('product_name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('qty_sold')
                    ->label('Qty Sold')
                    ->numeric()
                    ->alignCenter()
                    ->sortable(),
                Tables\Columns\TextColumn::make('net_sales')
                    ->label('Net Sales')
                    ->money('php')
                    ->alignCenter()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('branch_id')
                    ->label('Branch')
                    ->options(Branch::pluck('name', 'id')->toArray())
                    ->searchable(),
                SelectFilter::make('year_no')
                    ->label('Year')
                    ->options(function () {
                        $currentYear = now()->year;
                        $years = [];
                        for ($i = $currentYear - 5; $i <= $currentYear + 1; $i++) {
                            $years[$i] = $i;
                        }
                        return $years;
                    }),
                SelectFilter::make('month_no')
                    ->label('Month')
                    ->options([
                        1 => 'January',
                        2 => 'February',
                        3 => 'March',
                        4 => 'April',
                        5 => 'May',
                        6 => 'June',
                        7 => 'July',
                        8 => 'August',
                        9 => 'September',
                        10 => 'October',
                        11 => 'November',
                        12 => 'December',
                    ]),
            ])
            ->filtersFormColumns(1)
            ->defaultSort('qty_sold', 'desc')
            ->headerActions([
                ExportAction::make()
                    ->exports([
                        BestSellerReportExporter::make()
                            ->fromTable()
                            ->withColumns([
                                Column::make('branch.name')
                                    ->heading('Branch'),
                                Column::make('product_name')
                                    ->heading('Product'),
                                Column::make('qty_sold')
                                    ->heading('Qty Sold'),
                                Column::make('net_sales')
                                    ->heading('Net Sales'),
                            ])
                    ])
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBestSellerReports::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
