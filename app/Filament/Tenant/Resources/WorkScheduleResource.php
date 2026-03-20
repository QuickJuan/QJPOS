<?php

namespace App\Filament\Tenant\Resources;

use App\Filament\Tenant\Resources\WorkScheduleResource\Pages;
use App\Models\Attendance;
use App\Models\WorkSchedule;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WorkScheduleResource extends Resource
{
    protected static ?string $model = WorkSchedule::class;

    protected static ?string $navigationIcon  = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Human Resource';
    protected static ?string $navigationLabel = 'Work Schedules';
    protected static ?int    $navigationSort  = 15;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Schedule Assignment')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('Employee')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Select::make('branch_id')
                        ->label('Branch')
                        ->relationship('branch', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->placeholder('— All branches —'),

                    Forms\Components\Select::make('schedule_type_id')
                        ->label('Shift Type')
                        ->options(function () {
                            return \App\Models\ScheduleType::all()->mapWithKeys(fn ($t) =>
                                [$t->id => "{$t->name} — " . \Carbon\Carbon::createFromTimeString($t->expected_time_in)->format('g:i A') . " ({$t->duration_hours} hrs)"]
                            );
                        })
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\DatePicker::make('date')
                        ->label('Date')
                        ->required()
                        ->default(today())
                        ->displayFormat('M d, Y'),

                    Forms\Components\Textarea::make('notes')
                        ->label('Notes')
                        ->placeholder('Optional — schedule changes, special instructions, etc.')
                        ->rows(2)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date('D, M d Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branch')
                    ->placeholder('—')
                    ->sortable(),

                Tables\Columns\TextColumn::make('scheduleType.name')
                    ->label('Shift')
                    ->badge()
                    ->color(fn (WorkSchedule $record): string => 'gray'),

                Tables\Columns\TextColumn::make('scheduleType.expected_time_in')
                    ->label('Expected In')
                    ->formatStateUsing(fn ($state) => $state ? Carbon::createFromTimeString($state)->format('g:i A') : '—'),

                // Lateness indicator — compare actual_timein from attendance
                Tables\Columns\TextColumn::make('attendance_status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(function (WorkSchedule $record): string {
                        $attendance = Attendance::where('user_id', $record->user_id)
                            ->whereDate('attendance_date', $record->date)
                            ->first();

                        if (! $attendance) return 'No record';
                        if (! $attendance->actual_timein) return 'Not clocked in';

                        $expected = Carbon::parse(
                            $record->date->format('Y-m-d') . ' ' . $record->scheduleType->expected_time_in
                        );
                        $actual = Carbon::parse($attendance->actual_timein);

                        $diffMinutes = $actual->diffInMinutes($expected, false); // positive = early, negative = late

                        if ($diffMinutes >= 0) {
                            return 'On time';
                        }

                        $lateMinutes = abs($diffMinutes);
                        return "Late {$lateMinutes} min";
                    })
                    ->color(fn (string $state): string => match (true) {
                        str_starts_with($state, 'On time') => 'success',
                        str_starts_with($state, 'Late')    => 'danger',
                        str_starts_with($state, 'Not')     => 'warning',
                        default                            => 'gray',
                    }),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('schedule_type_id')
                    ->label('Shift Type')
                    ->relationship('scheduleType', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('branch_id')
                    ->label('Branch')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('From'),
                        Forms\Components\DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'],  fn ($q) => $q->whereDate('date', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('date', '<=', $data['until']));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListWorkSchedules::route('/'),
            'create' => Pages\CreateWorkSchedule::route('/create'),
            'edit'   => Pages\EditWorkSchedule::route('/{record}/edit'),
        ];
    }
}
