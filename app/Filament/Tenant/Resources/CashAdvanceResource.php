<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\CashAdvanceResource\Pages;
use App\Filament\Tenant\Resources\CashAdvanceResource\RelationManagers\CashAdvanceSchedulesRelationManager;
use App\Models\CashAdvance;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CashAdvanceResource extends Resource
{
    protected static ?string $model = CashAdvance::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'HR and Payroll';

    protected static ?int $navigationSort = 35;

    protected static ?string $label = 'Cash Advance';

    protected static ?string $pluralLabel = 'Cash Advances';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('employee_id')
                    ->label('Employee')
                    ->relationship('employee', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->user?->name ?? "Employee #{$record->id}")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('amount')
                    ->label('Amount Requested (₱)')
                    ->numeric()
                    ->step(0.01)
                    ->minValue(1)
                    ->required()
                    ->prefix('₱'),

                TextInput::make('terms')
                    ->label('Number of Terms (Pay Periods)')
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->maxValue(36)
                    ->required()
                    ->default(1)
                    ->helperText('How many payroll periods to spread the deduction'),

                DatePicker::make('start_deduction_date')
                    ->label('First Deduction Date')
                    ->nullable()
                    ->helperText('Leave blank — set when approving'),

                Select::make('status')
                    ->label('Status')
                    ->options(CashAdvance::statusOptions())
                    ->default('pending')
                    ->required()
                    ->disabled(fn ($record) => $record !== null), // managed via actions

                Textarea::make('reason')
                    ->label('Reason / Purpose')
                    ->nullable()
                    ->rows(3)
                    ->columnSpanFull(),

                Textarea::make('admin_notes')
                    ->label('Admin Notes')
                    ->nullable()
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.user.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('PHP')
                    ->sortable(),

                TextColumn::make('terms')
                    ->label('Terms')
                    ->suffix(' periods')
                    ->sortable(),

                TextColumn::make('amount_per_term')
                    ->label('Per Term')
                    ->money('PHP')
                    ->placeholder('–')
                    ->sortable(),

                TextColumn::make('start_deduction_date')
                    ->label('First Deduction')
                    ->date()
                    ->placeholder('Not set')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'pending'   => 'warning',
                        'approved'  => 'success',
                        'rejected'  => 'danger',
                        'completed' => 'gray',
                        default     => 'gray',
                    }),

                TextColumn::make('schedules_count')
                    ->label('Terms Generated')
                    ->counts('schedules')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Requested')
                    ->date()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(CashAdvance::statusOptions()),

                SelectFilter::make('employee_id')
                    ->label('Employee')
                    ->relationship('employee.user', 'name'),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (CashAdvance $record) => $record->status === 'pending')
                    ->form([
                        DatePicker::make('start_deduction_date')
                            ->label('First Deduction Date')
                            ->required()
                            ->default(fn (CashAdvance $record) =>
                                $record->start_deduction_date
                                    ? $record->start_deduction_date->toDateString()
                                    : now()->startOfMonth()->addMonth()->toDateString()
                            ),
                        Textarea::make('admin_notes')
                            ->label('Admin Notes')
                            ->nullable()
                            ->rows(2),
                    ])
                    ->action(function (CashAdvance $record, array $data) {
                        $terms = max(1, (int) $record->terms);
                        $amountPerTerm = round((float) $record->amount / $terms, 2);

                        $record->update([
                            'status'               => 'approved',
                            'approved_by'          => auth()->id(),
                            'approved_at'          => now(),
                            'amount_per_term'      => $amountPerTerm,
                            'start_deduction_date' => $data['start_deduction_date'],
                            'admin_notes'          => $data['admin_notes'] ?? $record->admin_notes,
                        ]);

                        // Delete any previously generated schedule (re-approval scenario)
                        $record->schedules()->delete();

                        $startDate = Carbon::parse($data['start_deduction_date']);

                        for ($i = 1; $i <= $terms; $i++) {
                            $record->schedules()->create([
                                'term_number' => $i,
                                'due_date'    => $startDate->copy()->addMonths($i - 1),
                                'amount'      => $amountPerTerm,
                                'paid_amount' => 0,
                                'status'      => 'pending',
                            ]);
                        }

                        Notification::make()
                            ->title('Cash advance approved')
                            ->body("₱" . number_format($record->amount, 2) . " over {$terms} term(s) — deduction schedule generated.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (CashAdvance $record) => $record->status === 'pending')
                    ->form([
                        Textarea::make('admin_notes')
                            ->label('Reason for Rejection')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (CashAdvance $record, array $data) {
                        $record->update([
                            'status'      => 'rejected',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                            'admin_notes' => $data['admin_notes'],
                        ]);

                        Notification::make()
                            ->title('Cash advance rejected')
                            ->danger()
                            ->send();
                    }),

                Tables\Actions\Action::make('mark_completed')
                    ->label('Mark Completed')
                    ->icon('heroicon-o-flag')
                    ->color('gray')
                    ->visible(fn (CashAdvance $record) => $record->status === 'approved')
                    ->requiresConfirmation()
                    ->action(fn (CashAdvance $record) => $record->update(['status' => 'completed'])),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CashAdvanceSchedulesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCashAdvances::route('/'),
            'create' => Pages\CreateCashAdvance::route('/create'),
            'view'   => Pages\ViewCashAdvance::route('/{record}/view'),
            'edit'   => Pages\EditCashAdvance::route('/{record}/edit'),
        ];
    }
}
