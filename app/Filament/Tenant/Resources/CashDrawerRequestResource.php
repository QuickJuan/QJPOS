<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\CashDrawerRequestResource\Pages;
use App\Models\CashierCashout;
use App\Services\CashierCashoutService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CashDrawerRequestResource extends Resource
{
    protected static ?string $model = CashierCashout::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Cash Drawer Approvals';

    protected static ?string $navigationGroup = 'Store';

    protected static ?string $slug = 'cash-drawer-requests';

    public static function shouldRegisterNavigation(): bool
    {
        try {
            $user = auth()->user();

            if (! $user) {
                return false;
            }

            return $user->hasAnyRole(['Admin', 'Manager']);
        } catch (\Exception $e) {
            // If roles table is not accessible, don't show navigation
            return false;
        }
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::pending()->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function form(Form $form): Form
    {
        return $form;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime('M d, Y h:ia')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('cashier.name')
                    ->label('Cashier')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->colors([
                        'warning' => CashierCashout::TYPE_CASH_OUT,
                        'success' => CashierCashout::TYPE_CASH_IN,
                    ])
                    ->formatStateUsing(fn (string $state): string => $state === CashierCashout::TYPE_CASH_IN ? 'Cash In' : 'Cash Out'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('PHP', true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('source_name')
                    ->label('Source / Person')
                    ->wrap(),
                Tables\Columns\TextColumn::make('purpose')
                    ->label('Purpose')
                    ->limit(40),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'warning' => CashierCashout::STATUS_PENDING,
                        'success' => CashierCashout::STATUS_APPROVED,
                        'danger' => CashierCashout::STATUS_REJECTED,
                    ])
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        CashierCashout::STATUS_PENDING => 'Pending',
                        CashierCashout::STATUS_APPROVED => 'Approved',
                        CashierCashout::STATUS_REJECTED => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        CashierCashout::TYPE_CASH_OUT => 'Cash Out',
                        CashierCashout::TYPE_CASH_IN => 'Cash In',
                    ]),
                Tables\Filters\Filter::make('submitted_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Submitted From'),
                        Forms\Components\DatePicker::make('until')->label('Submitted Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->visible(fn (CashierCashout $record): bool => $record->isPending())
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Approval Notes')
                            ->rows(3)
                            ->maxLength(1000),
                    ])
                    ->action(function (CashierCashout $record, array $data): void {
                        app(CashierCashoutService::class)->approve($record, auth()->user(), $data['notes'] ?? null);
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->visible(fn (CashierCashout $record): bool => $record->isPending())
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Reason')
                            ->rows(3)
                            ->required()
                            ->maxLength(1000),
                    ])
                    ->action(function (CashierCashout $record, array $data): void {
                        app(CashierCashoutService::class)->reject($record, auth()->user(), $data['notes'] ?? null);
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCashDrawerRequests::route('/'),
        ];
    }
}
