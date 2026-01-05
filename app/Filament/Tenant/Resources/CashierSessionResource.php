<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\CashierSessionResource\Pages;
use App\Models\CashierSession;
use App\Models\Branch;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

class CashierSessionResource extends Resource
{
    protected static ?string $model = CashierSession::class;
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationLabel = 'Cashier Shifts';
    protected static ?string $modelLabel = 'X-Reading (Cashier Shift)';
    protected static ?string $pluralModelLabel = 'X-Reading (Cashier Shifts)';
    protected static ?string $navigationGroup = 'Sales Report';
    protected static ?int $navigationSort = 15;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('business_date')
                    ->label('Business Date')
                    ->date('M d, Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cashier.name')
                    ->label('Cashier')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('started_time')
                    ->label('Started')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('closing_time')
                    ->label('Closed')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->placeholder('-- Still Open ---'),
                Tables\Columns\TextColumn::make('beginning_cash')
                    ->label('Beginning Cash')
                    ->money('php')
                    ->alignRight(),
                Tables\Columns\TextColumn::make('total_sales')
                    ->label('Total Sales')
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
                SelectFilter::make('cashier_id')
                    ->label('Cashier')
                    ->options(User::whereNotNull('id')->pluck('name', 'id')->toArray())
                    ->searchable(),
                Filter::make('business_date')
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
                                fn (Builder $query, $date): Builder => $query->whereDate('business_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('business_date', '<=', $date),
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
            ->defaultSort('business_date', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCashierSessions::route('/'),
            'view' => Pages\ViewCashierSession::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
