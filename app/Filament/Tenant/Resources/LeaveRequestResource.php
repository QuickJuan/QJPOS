<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\LeaveRequestResource\Pages;
use App\Models\Employee;
use App\Models\EmployeeLeaveCredit;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeaveRequestResource extends Resource
{
    protected static ?string $model = LeaveRequest::class;

    protected static ?string $navigationIcon  = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'HR and Payroll';
    protected static ?int    $navigationSort  = 37;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Leave Request Details')->schema([
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\Select::make('employee_id')
                        ->label('Employee')
                        ->options(
                            Employee::with('user')
                                ->get()
                                ->mapWithKeys(fn ($e) => [$e->id => $e->user?->name ?? "Employee #{$e->id}"])
                        )
                        ->searchable()
                        ->required(),

                    Forms\Components\Select::make('leave_type_id')
                        ->label('Leave Type')
                        ->options(LeaveType::where('is_active', true)->orderBy('sort_order')->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                ]),

                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\DatePicker::make('start_date')
                        ->label('Start Date')
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, $get, $set) {
                            $end = $get('end_date');
                            if ($state && $end) {
                                $days = Carbon::parse($state)->diffInDays(Carbon::parse($end)) + 1;
                                $set('days_requested', max(1, $days));
                            }
                        }),

                    Forms\Components\DatePicker::make('end_date')
                        ->label('End Date')
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, $get, $set) {
                            $start = $get('start_date');
                            if ($start && $state) {
                                $days = Carbon::parse($start)->diffInDays(Carbon::parse($state)) + 1;
                                $set('days_requested', max(1, $days));
                            }
                        }),

                    Forms\Components\TextInput::make('days_requested')
                        ->label('Days Requested')
                        ->numeric()
                        ->required()
                        ->minValue(0.5)
                        ->step(0.5)
                        ->helperText('0.5 = half day. Auto-computed from dates but can be adjusted.'),
                ]),

                Forms\Components\Toggle::make('is_half_day')
                    ->label('Half Day')
                    ->helperText('Toggle if only half of the first day is taken.')
                    ->live()
                    ->afterStateUpdated(function ($state, $get, $set) {
                        if ($state) {
                            $set('days_requested', 0.5);
                        }
                    }),

                Forms\Components\Textarea::make('reason')
                    ->label('Reason / Details')
                    ->rows(3)
                    ->columnSpanFull(),
            ]),

            Forms\Components\Section::make('Admin')->schema([
                Forms\Components\Select::make('status')
                    ->options([
                        'pending'   => 'Pending',
                        'approved'  => 'Approved',
                        'rejected'  => 'Rejected',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('pending')
                    ->required(),

                Forms\Components\Textarea::make('admin_notes')
                    ->label('Admin Notes')
                    ->rows(2),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('employee.user.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('leaveType.name')
                    ->label('Leave Type')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('From')
                    ->date('M d, Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('To')
                    ->date('M d, Y'),

                Tables\Columns\TextColumn::make('days_requested')
                    ->label('Days')
                    ->numeric(decimalPlaces: 1)
                    ->sortable(),

                Tables\Columns\TextColumn::make('days_with_pay')
                    ->label('Paid Days')
                    ->numeric(decimalPlaces: 1)
                    ->color('success')
                    ->placeholder('—'),

                Tables\Columns\BadgeColumn::make('days_without_pay')
                    ->label('LWOP')
                    ->formatStateUsing(fn ($state) => $state > 0 ? number_format($state, 1) . 'd LWOP' : '—')
                    ->colors([
                        'danger' => fn ($state) => $state > 0,
                        'gray'   => fn ($state) => $state == 0 || $state === null,
                    ]),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger'  => 'rejected',
                        'gray'    => 'cancelled',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Filed')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending'   => 'Pending',
                        'approved'  => 'Approved',
                        'rejected'  => 'Rejected',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Filters\SelectFilter::make('leave_type_id')
                    ->label('Leave Type')
                    ->relationship('leaveType', 'name'),

                Tables\Filters\Filter::make('has_lwop')
                    ->label('Has LWOP')
                    ->query(fn (Builder $query) => $query->where('days_without_pay', '>', 0)),

                Tables\Filters\Filter::make('this_year')
                    ->label('This Year')
                    ->query(fn (Builder $query) => $query->whereYear('start_date', now()->year))
                    ->default(),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (LeaveRequest $record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Admin Notes (optional)')
                            ->rows(2),
                    ])
                    ->action(function (LeaveRequest $record, array $data) {
                        $year = Carbon::parse($record->start_date)->year;

                        $credit = EmployeeLeaveCredit::query()
                            ->where('employee_id', $record->employee_id)
                            ->where('leave_type_id', $record->leave_type_id)
                            ->where('year', $year)
                            ->first();

                        $remaining    = $credit ? max(0, (float) $credit->total_days - (float) $credit->used_days) : 0;
                        $daysWithPay  = min((float) $record->days_requested, $remaining);
                        $daysWithoutPay = (float) $record->days_requested - $daysWithPay;

                        if ($credit && $daysWithPay > 0) {
                            $credit->increment('used_days', $daysWithPay);
                        }

                        $record->update([
                            'status'           => 'approved',
                            'approved_by'      => auth()->id(),
                            'approved_at'      => now(),
                            'days_with_pay'    => $daysWithPay,
                            'days_without_pay' => $daysWithoutPay,
                            'admin_notes'      => $data['admin_notes'] ?? $record->admin_notes,
                        ]);

                        $msg = $daysWithoutPay > 0
                            ? "{$daysWithPay}d paid + {$daysWithoutPay}d LWOP"
                            : "{$daysWithPay}d fully paid";

                        Notification::make()
                            ->title('Leave approved')
                            ->body($msg)
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (LeaveRequest $record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Reason for Rejection')
                            ->required()
                            ->rows(2),
                    ])
                    ->action(function (LeaveRequest $record, array $data) {
                        $record->update([
                            'status'      => 'rejected',
                            'admin_notes' => $data['admin_notes'],
                        ]);

                        Notification::make()
                            ->title('Leave request rejected')
                            ->warning()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLeaveRequests::route('/'),
            'create' => Pages\CreateLeaveRequest::route('/create'),
            'view'   => Pages\ViewLeaveRequest::route('/{record}/view'),
            'edit'   => Pages\EditLeaveRequest::route('/{record}/edit'),
        ];
    }
}
