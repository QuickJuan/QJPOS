<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\RefundOrdersReportResource\Pages;
use App\Models\Order;
use App\Models\Branch;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class RefundOrdersReportResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-left';
    protected static ?string $navigationLabel = 'Refund Orders Report';
    protected static ?string $modelLabel = 'Refund Order';
    protected static ?string $pluralModelLabel = 'Refund Orders';
    protected static ?string $navigationGroup = 'Sales Report';
    protected static ?int $navigationSort = 17;

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'refund'))
            ->columns([
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->date('M d, Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoice_no')
                    ->label('Order #')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->default('Walk-in'),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Subtotal')
                    ->money('php')
                    ->alignRight(),
                Tables\Columns\TextColumn::make('item_discount')
                    ->label('Discount')
                    ->money('php')
                    ->alignRight()
                    ->state(fn ($record) => $record->item_discount + $record->less_tax),
                Tables\Columns\TextColumn::make('service_charge')
                    ->label('Service Charge')
                    ->money('php')
                    ->alignRight(),

                Tables\Columns\TextColumn::make('total_due')
                    ->label('Total Due')
                    ->money('php')
                    ->alignRight()
                    ->state(fn ($record) => $record->total_due + $record->service_charge),

                Tables\Columns\TextColumn::make('cashier.name')
                    ->label('Cashier')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('branch_id')
                    ->label('Branch')
                    ->options(Branch::pluck('name', 'id')->toArray())
                    ->searchable(),
                SelectFilter::make('payment_method')
                    ->label('Payment Method')
                    ->options([
                        'cash' => 'Cash',
                        'card' => 'Card',
                        'gcash' => 'GCash',
                        'maya' => 'Maya',
                    ]),
                Filter::make('created_at')
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
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
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
            ->bulkActions([
                ExportBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRefundOrdersReports::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
