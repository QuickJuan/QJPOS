<?php

namespace App\Filament\Tenant\Resources\WorkScheduleResource\Pages;

use App\Filament\Tenant\Resources\WorkScheduleResource;
use App\Models\ScheduleType;
use App\Models\WorkSchedule;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateWorkSchedule extends CreateRecord
{
    protected static string $resource = WorkScheduleResource::class;

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Bulk Schedule Assignment')
                ->description('Assign a shift to an employee for a date range. One schedule record is created per day.')
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
                            return ScheduleType::all()->mapWithKeys(fn ($t) =>
                                [$t->id => "{$t->name} — " . Carbon::createFromTimeString($t->expected_time_in)->format('g:i A') . " ({$t->duration_hours} hrs)"]
                            );
                        })
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\DatePicker::make('date_from')
                        ->label('From')
                        ->required()
                        ->default(today())
                        ->displayFormat('M d, Y'),

                    Forms\Components\DatePicker::make('date_to')
                        ->label('To')
                        ->required()
                        ->default(today())
                        ->displayFormat('M d, Y')
                        ->after('date_from'),

                    Forms\Components\CheckboxList::make('days_of_week')
                        ->label('Days to Include')
                        ->options([
                            1 => 'Monday',
                            2 => 'Tuesday',
                            3 => 'Wednesday',
                            4 => 'Thursday',
                            5 => 'Friday',
                            6 => 'Saturday',
                            0 => 'Sunday',
                        ])
                        ->default([1, 2, 3, 4, 5])
                        ->columns(4)
                        ->gridDirection('row')
                        ->columnSpanFull()
                        ->helperText('Uncheck days that should not be scheduled (e.g. rest days)'),

                    Forms\Components\Toggle::make('overwrite_existing')
                        ->label('Overwrite existing schedules for these dates')
                        ->default(false)
                        ->helperText('If off, dates that already have a schedule will be skipped')
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('notes')
                        ->label('Notes')
                        ->placeholder('Optional — applies to all created schedule entries')
                        ->rows(2)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    protected function handleRecordCreation(array $data): Model
    {
        $dateFrom      = Carbon::parse($data['date_from'])->startOfDay();
        $dateTo        = Carbon::parse($data['date_to'])->startOfDay();
        $daysOfWeek    = array_map('intval', $data['days_of_week'] ?? [0,1,2,3,4,5,6]);
        $overwrite     = (bool) ($data['overwrite_existing'] ?? false);

        $period  = CarbonPeriod::create($dateFrom, $dateTo);
        $created = 0;
        $skipped = 0;
        $first   = null;

        foreach ($period as $day) {
            // Filter by selected days of week (Carbon: 0=Sunday … 6=Saturday)
            if (! in_array($day->dayOfWeek, $daysOfWeek)) {
                continue;
            }

            $attributes = [
                'user_id' => $data['user_id'],
                'date'    => $day->toDateString(),
            ];

            if ($overwrite) {
                $record = WorkSchedule::updateOrCreate($attributes, [
                    'branch_id'        => $data['branch_id'],
                    'schedule_type_id' => $data['schedule_type_id'],
                    'notes'            => $data['notes'] ?? null,
                ]);
                $created++;
            } else {
                $exists = WorkSchedule::where($attributes)->exists();
                if ($exists) {
                    $skipped++;
                    continue;
                }
                $record = WorkSchedule::create(array_merge($attributes, [
                    'branch_id'        => $data['branch_id'],
                    'schedule_type_id' => $data['schedule_type_id'],
                    'notes'            => $data['notes'] ?? null,
                ]));
                $created++;
            }

            if ($first === null) {
                $first = $record;
            }
        }

        if ($first === null) {
            // Nothing created — all skipped; return a dummy so Filament doesn't crash
            $first = new WorkSchedule($data);
        }

        $body = "{$created} schedule(s) created" . ($skipped > 0 ? ", {$skipped} skipped (already existed)" : '') . '.';

        Notification::make()
            ->title('Schedules saved')
            ->body($body)
            ->success()
            ->send();

        return $first;
    }

    // After creation go back to the list, not the edit page for one record
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // Suppress the default "Created" notification (we send our own above)
    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        return null;
    }
}
