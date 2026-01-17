<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\SalesByCashierServerResource\Pages;
use App\Filament\Tenant\Exports\SalesByCashierServer\SalesByCashierServerExporter;
use App\Models\SalesByCashierServer;
use App\Models\Branch;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;

class SalesByCashierServerResource extends Resource
{
    protected static ?string $model = SalesByCashierServer::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Sales by Cashier/Server';
    protected static ?string $navigationGroup = 'Sales Report';
    protected static ?int $navigationSort = 16;

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
                Tables\Columns\TextColumn::make('server_name')
                    ->label('Server')
                    ->searchable()
                    ->sortable()
                    ->default('N/A'),
                Tables\Columns\TextColumn::make('cashier_name')
                    ->label('Cashier')
                    ->searchable()
                    ->sortable()
                    ->default('N/A'),
                Tables\Columns\TextColumn::make('gross')
                    ->label('Gross')
                    ->money('php')
                    ->alignRight()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_amount')
                    ->label('Discount + Less taax')
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
                SelectFilter::make('server_id')
                    ->label('Server')
                    ->options(User::whereNotNull('id')->pluck('name', 'id')->toArray())
                    ->searchable(),
                SelectFilter::make('cashier_id')
                    ->label('Cashier')
                    ->options(User::whereNotNull('id')->pluck('name', 'id')->toArray())
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
            ->filtersFormColumns(2)
            ->defaultSort('sale_date', 'desc')
            ->headerActions([
                ExportAction::make()
                    ->exports([
                        SalesByCashierServerExporter::make()
                            ->fromTable()
                            ->withColumns([
                                Column::make('branch.name')
                                    ->heading('Branch'),
                                Column::make('sale_date')
                                    ->heading('Date')
                                    ->formatStateUsing(fn ($state) => \Carbon\Carbon::parse($state)->format('M d, Y')),
                                Column::make('server_name')
                                    ->heading('Server'),
                                Column::make('cashier_name')
                                    ->heading('Cashier'),
                                Column::make('gross')
                                    ->heading('Gross'),
                                Column::make('discount_amount')
                                    ->heading('Discount + Less taax'),
                                Column::make('sub_total')
                                    ->heading('Sub Total'),
                            ])
                    ])
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesByCashierServers::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
