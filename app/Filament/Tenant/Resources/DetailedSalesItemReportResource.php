<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Exports\DetailedSalesItemReport\DetailedSalesItemReportExporter;
use App\Filament\Tenant\Resources\DetailedSalesItemReportResource\Pages;
use App\Models\Branch;
use App\Models\OrderItem;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;

class DetailedSalesItemReportResource extends Resource
{
    protected static ?string $model = OrderItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';
    protected static ?string $navigationLabel = 'Detailed Sales Item Report';
    protected static ?string $modelLabel = 'Detailed Sale Item';
    protected static ?string $pluralModelLabel = 'Detailed Sale Items';
    protected static ?string $navigationGroup = 'Sales Report';
    protected static ?int $navigationSort = 15;

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                return $query
                    ->with([
                        'product',
                        'productPackaging',
                        'order.branch',
                        'order.cashier',
                        'order.customer',
                    ])
                    ->whereHas('order')
                    ->orderByDesc('order_id')
                    ->orderByRaw('COALESCE(NULLIF(parent_id, 0), id)')
                        ->orderByRaw('CASE WHEN parent_id IS NULL OR parent_id = 0 THEN 0 ELSE 1 END')
                    ->orderBy('id');
            })
            ->columns([

                Tables\Columns\TextColumn::make('id')
                    ->label('Rec. Id')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('parent_id')
                    ->label('Parent Rec. Id')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('order.branch.name')
                    ->label('Branch')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('order.created_at')
                    ->label('Order Date')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),

                Tables\Columns\TextColumn::make('order.invoice_no')
                    ->label('Receipt #')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('order_type')
                    ->label('Order Type')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state
                        ? ucwords(str_replace('-', ' ', $state))
                        : '-')
                    ->color(fn (?string $state): string => match ($state) {
                        'dine-in' => 'success',
                        'takeout' => 'warning',
                        'delivery' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Qty')
                    ->numeric()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->alignRight()
                    ->sortable()
                    ->formatStateUsing(function (OrderItem $record): string {
                        $price = (float) ($record->price ?? 0);

                        $isChild = ! empty($record->parent_id);
                        if ($isChild && $price <= 0) {
                            return 'Package item';
                        }

                        return '₱' . number_format($price, 2);
                    }),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('php')
                    ->alignRight()
                    ->sortable(),

                Tables\Columns\TextColumn::make('item_discount')
                    ->label('Discount')
                    ->money('php')
                    ->alignRight()
                    ->sortable(),

                Tables\Columns\TextColumn::make('less_tax')
                    ->label('Less Tax')
                    ->money('php')
                    ->alignRight()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sub_total')
                    ->label('Sub Total')
                    ->money('php')
                    ->alignRight()
                    ->sortable(),



                Tables\Columns\TextColumn::make('order.cashier.name')
                    ->label('Cashier')
                    ->searchable(),

                Tables\Columns\TextColumn::make('order.status')
                    ->label('Status')
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
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'] ?? null,
                        fn (Builder $query, $branchId): Builder => $query->whereHas('order', fn (Builder $orderQuery) => $orderQuery->where('branch_id', $branchId)),
                    )),

                SelectFilter::make('status')
                    ->options([
                        'settled' => 'Settled',
                        'refund' => 'Refund',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'] ?? null,
                        fn (Builder $query, $status): Builder => $query->whereHas('order', fn (Builder $orderQuery) => $orderQuery->where('status', $status)),
                    )),

                Filter::make('order_created_at')
                    ->label('Order Date')
                    ->form([
                        DatePicker::make('from')->label('From Date'),
                        DatePicker::make('until')->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $from = $data['from'] ?? null;
                        $until = $data['until'] ?? null;

                        return $query
                            ->when(
                                $from,
                                fn (Builder $query, $date): Builder => $query->whereHas('order', fn (Builder $orderQuery) => $orderQuery->whereDate('created_at', '>=', $date)),
                            )
                            ->when(
                                $until,
                                fn (Builder $query, $date): Builder => $query->whereHas('order', fn (Builder $orderQuery) => $orderQuery->whereDate('created_at', '<=', $date)),
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
            ->defaultSort('order_id', 'desc')
            ->headerActions([
                ExportAction::make()
                    ->exports([
                        DetailedSalesItemReportExporter::make()->fromTable(),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDetailedSalesItemReports::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
