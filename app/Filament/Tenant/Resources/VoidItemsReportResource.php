<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\VoidItemsReportResource\Pages;
use App\Models\OrderItem;
use App\Models\Branch;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

class VoidItemsReportResource extends Resource
{
    protected static ?string $model = OrderItem::class;
    protected static ?string $navigationIcon = 'heroicon-o-x-circle';
    protected static ?string $navigationLabel = 'Void Items Report';
    protected static ?string $modelLabel = 'Void Item';
    protected static ?string $pluralModelLabel = 'Void Items';
    protected static ?string $navigationGroup = 'Sales Report';
    protected static ?int $navigationSort = 16;

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('is_void', 1)->with(['order', 'product', 'server']))
            ->columns([
                Tables\Columns\TextColumn::make('order.branch.name')
                    ->label('Branch')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order.created_at')
                    ->label('Date')
                    ->date('M d, Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Order #')
                    ->searchable()
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
                    ->money('php')
                    ->alignRight(),
                Tables\Columns\TextColumn::make('sub_total')
                    ->label('Amount')
                    ->money('php')
                    ->alignRight()
                    ->color('danger')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('order.cashier.name')
                    ->label('Cashier')
                    ->searchable()
                    ->default('N/A'),
                Tables\Columns\TextColumn::make('server.name')
                    ->label('Server')
                    ->searchable()
                    ->default('N/A'),
                Tables\Columns\TextColumn::make('void_reason')
                    ->label('Void Reason')
                    ->searchable()
                    ->wrap(),
            ])
            ->filters([
                SelectFilter::make('order.branch_id')
                    ->label('Branch')
                    ->relationship('order.branch', 'name')
                    ->searchable(),
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
                                fn (Builder $query, $date): Builder => $query->whereHas('order', fn ($q) => $q->whereDate('created_at', '>=', $date)),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereHas('order', fn ($q) => $q->whereDate('created_at', '<=', $date)),
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
            ->defaultSort('order.created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVoidItemsReports::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
