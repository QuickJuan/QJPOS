<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\SalesByDateResource\Pages;
use App\Filament\Tenant\Exports\SalesByDate\SalesByDateExporter;
use App\Models\SalesByDate;
use App\Models\Branch;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;

class SalesByDateResource extends Resource
{
    protected static ?string $model = SalesByDate::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Sales by Date';
    protected static ?string $navigationGroup = 'Order Reports';
    protected static ?int $navigationSort = 13;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable()
                    ->sortable()
                    ->default('N/A'),
                Tables\Columns\TextColumn::make('sale_date')
                    ->label('Date')
                    ->date('M d, Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sold')
                    ->label('Sold')
                    ->numeric()
                    ->alignCenter()
                    ->sortable(),
                Tables\Columns\TextColumn::make('gross')
                    ->label('Gross')
                    ->money('php')
                    ->alignRight()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_amount')
                    ->label('Discount + Less')
                    ->money('php')
                    ->alignRight()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sub_total')
                    ->label('Sub Total')
                    ->money('php')
                    ->alignRight()
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),
            ])
            ->filters([
                SelectFilter::make('branch_id')
                    ->label('Branch')
                    ->options(Branch::pluck('name', 'id')->toArray())
                    ->searchable(),
                Filter::make('sale_date')
                    ->form([
                        DatePicker::make('from')
                            ->label('From Date'),
                        DatePicker::make('until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('sale_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('sale_date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators[] = 'From: ' . \Carbon\Carbon::parse($data['from'])->toFormattedDateString();
                        }
                        if ($data['until'] ?? null) {
                            $indicators[] = 'Until: ' . \Carbon\Carbon::parse($data['until'])->toFormattedDateString();
                        }
                        return $indicators;
                    }),
            ])
            ->filtersFormColumns(1)
            ->defaultSort('sale_date', 'desc')
            ->headerActions([
                ExportAction::make()
                    ->exports([
                        SalesByDateExporter::make()
                            ->fromTable()
                            ->withColumns([
                                Column::make('branch.name')
                                    ->heading('Branch'),
                                Column::make('sale_date')
                                    ->heading('Date')
                                    ->formatStateUsing(fn ($state) => \Carbon\Carbon::parse($state)->format('M d, Y')),
                                Column::make('sold')
                                    ->heading('Sold'),
                                Column::make('gross')
                                    ->heading('Gross'),
                                Column::make('discount_amount')
                                    ->heading('Discount + Less'),
                                Column::make('sub_total')
                                    ->heading('Sub Total'),
                            ])
                    ])
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesByDates::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
