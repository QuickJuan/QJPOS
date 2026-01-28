<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\SalesInvoiceReportResource\Pages;
use App\Models\Branch;
use App\Models\Order;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SalesInvoiceReportResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationLabel = 'Sales Invoice Report';
    protected static ?string $modelLabel = 'Sales Invoice';
    protected static ?string $pluralModelLabel = 'Sales Invoice Reports';
    protected static ?string $navigationGroup = 'Sales Report';
    protected static ?int $navigationSort = 16;

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query
                    ->with([
                        'branch',
                        'cashier',
                        'cashierSession',
                        'customer',
                        'payments.paymentMethod',
                        'orderItems.product',
                        'orderItems.productPackaging',
                    ])
                    ->withCount('orderItems');
            })
            ->columns([
                ViewColumn::make('invoice_block')
                    ->label('Invoice')
                    ->view('filament.tables.columns.sales-invoice-items')
                    ->state(fn (Order $record) => $record),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('invoice_no')
                    ->label('Receipt #')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('cashier.name')
                    ->label('Cashier')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->default('Walk-in')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'settled' => 'success',
                        'refund' => 'danger',
                        default => 'gray',
                    })
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('branch_id')
                    ->label('Branch')
                    ->options(Branch::pluck('name', 'id')->toArray())
                    ->searchable(),

                SelectFilter::make('cashier_id')
                    ->label('Cashier')
                    ->relationship('cashier', 'name')
                    ->preload()
                    ->searchable(),

                SelectFilter::make('cashier_session_id')
                    ->label('Shift #')
                    ->relationship('cashierSession', 'id')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status')
                    ->options([
                        'settled' => 'Settled',
                        'refund' => 'Refund',
                    ]),

                Filter::make('created_at')
                    ->label('Order Date')
                    ->form([
                        DatePicker::make('from')->label('From Date'),
                        DatePicker::make('until')->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
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
            ->defaultSort('created_at', 'desc')
            ->headerActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesInvoiceReports::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
