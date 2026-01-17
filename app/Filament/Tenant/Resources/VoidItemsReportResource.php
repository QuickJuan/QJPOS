<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Exports\VoidItemsReport\VoidItemsReportExporter;
use App\Filament\Tenant\Resources\VoidItemsReportResource\Pages;
use App\Models\VoidItem;
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

class VoidItemsReportResource extends Resource
{
    protected static ?string $model = VoidItem::class;
    protected static ?string $navigationIcon = 'heroicon-o-x-circle';
    protected static ?string $navigationLabel = 'Void Items Report';
    protected static ?string $modelLabel = 'Void Item';
    protected static ?string $pluralModelLabel = 'Void Items';
    protected static ?string $navigationGroup = 'Sales Report';
    protected static ?int $navigationSort = 18;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cashier.name')
                    ->label('Cashier')
                    ->searchable()
                    ->sortable()
                    ->default('N/A'),
                Tables\Columns\TextColumn::make('voided_at')
                    ->label('Date')
                    ->date('M d, Y h:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('batch_number')
                    ->label('Batch #')
                    ->searchable()
                    ->sortable()
                    ->default('N/A'),
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
                Tables\Columns\TextColumn::make('voidedBy.name')
                    ->label('Voided By')
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
                SelectFilter::make('cashier_id')
                    ->label('Cashier')
                    ->relationship('cashier', 'name')
                    ->searchable(),
                Filter::make('voided_at')
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
                                fn (Builder $query, $date): Builder => $query->whereDate('voided_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('voided_at', '<=', $date),
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
            ->defaultSort('voided_at', 'desc')
            ->headerActions([
                ExportAction::make()
                    ->exports([
                        VoidItemsReportExporter::make()->fromTable(),
                    ]),
            ]);
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
