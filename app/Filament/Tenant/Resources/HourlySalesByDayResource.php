<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\HourlySalesByDayResource\Pages;
use App\Filament\Tenant\Exports\HourlySalesByDay\HourlySalesByDayExporter;
use App\Models\HourlySalesByDay;
use App\Models\Branch;
use Carbon\Carbon;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;

class HourlySalesByDayResource extends Resource
{
    protected static ?string $model = HourlySalesByDay::class;
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Hourly Sales by Day';
    protected static ?string $navigationGroup = 'Sales Report';
    protected static ?int $navigationSort = 10;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable()
                    ->sortable()
                    ->default('N/A'),
                Tables\Columns\TextColumn::make('sale_hour')
                    ->label('Time')
                    ->formatStateUsing(fn ($state) => Carbon::createFromTime($state, 0)->format('g:00 A'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('monday_sales')
                    ->label('Monday')
                    ->money('php')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('tuesday_sales')
                    ->label('Tuesday')
                    ->money('php')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('wednesday_sales')
                    ->label('Wednesday')
                    ->money('php')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('thursday_sales')
                    ->label('Thursday')
                    ->money('php')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('friday_sales')
                    ->label('Friday')
                    ->money('php')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('saturday_sales')
                    ->label('Saturday')
                    ->money('php')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('sunday_sales')
                    ->label('Sunday')
                    ->money('php')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('total_sales')
                    ->label('Total')
                    ->money('php')
                    ->alignCenter()
                    ->weight('bold')
                    ->color('success'),
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
            ->defaultSort('sale_hour')
            ->paginated(false)
            ->headerActions([
                ExportAction::make()
                    ->exports([
                        HourlySalesByDayExporter::make()
                            ->fromTable()
                            ->withColumns([
                                Column::make('branch.name')
                                    ->heading('Branch'),
                                Column::make('sale_hour')
                                    ->heading('Time')
                                    ->formatStateUsing(fn ($state) => Carbon::createFromTime($state, 0)->format('g:00 A')),
                                Column::make('monday_sales')
                                    ->heading('Monday'),
                                Column::make('tuesday_sales')
                                    ->heading('Tuesday'),
                                Column::make('wednesday_sales')
                                    ->heading('Wednesday'),
                                Column::make('thursday_sales')
                                    ->heading('Thursday'),
                                Column::make('friday_sales')
                                    ->heading('Friday'),
                                Column::make('saturday_sales')
                                    ->heading('Saturday'),
                                Column::make('sunday_sales')
                                    ->heading('Sunday'),
                                Column::make('total_sales')
                                    ->heading('Total'),
                            ])
                    ])
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHourlySalesByDays::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
