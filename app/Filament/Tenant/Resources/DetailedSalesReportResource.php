<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Exports\DetailedSalesReport\DetailedSalesReportExporter;
use App\Filament\Tenant\Resources\DetailedSalesReportResource\Pages;
use App\Models\Branch;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Enums\PaymentType;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;

class DetailedSalesReportResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Detailed Sales Report';
    protected static ?string $modelLabel = 'Detailed Sale';
    protected static ?string $pluralModelLabel = 'Detailed Sales';
    protected static ?string $navigationGroup = 'Sales Report';
    protected static ?int $navigationSort = 14;

    public static function table(Table $table): Table
    {
        $paymentMethods = PaymentMethod::query()
            ->active()
            ->ordered()
            ->get(['id', 'name', 'payment_type']);

        $paymentColumns = $paymentMethods
            ->map(function (PaymentMethod $method) {
                $showByDefault = in_array($method->payment_type, [PaymentType::CASH, PaymentType::E_WALLET], true);

                return Tables\Columns\TextColumn::make('payment_method_' . $method->id)
                    ->label($method->name)
                    ->alignRight()
                    ->state(function (Order $record) use ($method) {
                        if (! $record->relationLoaded('payments')) {
                            return '0.00';
                        }

                        $payments = $record->payments->where('payment_method_id', $method->id);

                        $amountInPaymentCurrency = (float) $payments->sum(function ($payment) {
                            return (float) ($payment->amount_in_payment_currency ?? 0);
                        });

                        $exchangeRate = (float) $payments->max(function ($payment) {
                            return (float) ($payment->exchange_rate ?? 0);
                        });

                        $formattedAmount = number_format($amountInPaymentCurrency, 2);

                        if ($exchangeRate > 1) {
                            return $formattedAmount . ' (x' . number_format($exchangeRate, 4) . ')';
                        }

                        return $formattedAmount;
                    })
                            ->toggleable(isToggledHiddenByDefault: ! $showByDefault);
            })
            ->values()
            ->all();

        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query
                    ->with([
                        'branch',
                        'cashier',
                        'customer',
                        'payments.paymentMethod',
                    ]);
            })
            ->columns([
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),

                Tables\Columns\TextColumn::make('id')
                    ->label('Receipt #')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->default('Walk-in'),

                Tables\Columns\TextColumn::make('cashier.name')
                    ->label('Cashier')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Subtotal')
                    ->money('php')
                    ->alignRight()
                    ->sortable(),

                Tables\Columns\TextColumn::make('item_discount')
                    ->label('Item Discount')
                    ->money('php')
                    ->alignRight()
                    ->sortable(),

                Tables\Columns\TextColumn::make('less_tax')
                    ->label('Less Tax')
                    ->money('php')
                    ->alignRight()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_discount')
                    ->label('Total Discount')
                    ->money('php')
                    ->alignRight()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('vatable_sales')
                    ->label('Vatable Sales')
                    ->money('php')
                    ->alignRight()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('vat_amount')
                    ->label('VAT Amount')
                    ->money('php')
                    ->alignRight()
                    ->sortable(),

                Tables\Columns\TextColumn::make('service_charge')
                    ->label('Service Charge')
                    ->money('php')
                    ->alignRight()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total_due')
                    ->label('Total Due')
                    ->money('php')
                    ->alignRight()
                    ->sortable()
                    ->state(fn (Order $record) => (float) ($record->total_due ?? 0) + (float) ($record->service_charge ?? 0)),

                Tables\Columns\TextColumn::make('payments_total')
                    ->label('Payments Total')
                    ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 2))
                    ->alignRight()
                    ->state(function (Order $record) {
                        if (! $record->relationLoaded('payments')) {
                            return 0;
                        }

                        return $record->payments
                            ->sum(function ($payment) {
                                return (float) ($payment->amount_applied ?? $payment->amount ?? 0);
                            });
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                ...$paymentColumns,

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'settled' => 'success',
                        'refund' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
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

                SelectFilter::make('status')
                    ->options([
                        'settled' => 'Settled',
                        'refund' => 'Refund',
                    ]),

                Filter::make('created_at')
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
            ->headerActions([
                ExportAction::make()
                    ->exports([
                        DetailedSalesReportExporter::make()->fromTable(),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDetailedSalesReports::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
