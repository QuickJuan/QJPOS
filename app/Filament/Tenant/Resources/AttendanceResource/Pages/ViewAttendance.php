<?php
namespace App\Filament\Tenant\Resources\AttendanceResource\Pages;

use App\Filament\Tenant\Resources\AttendanceResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewAttendance extends ViewRecord
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make('edit'),

            DeleteAction::make('delete')
                ->successRedirectUrl(route('filament.tenant.resources.attendances.index')),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Attendance Details')
                    ->schema([
                        TextEntry::make('branch.name'),

                        TextEntry::make('user.name'),

                        TextEntry::make('attendance_date')
                            ->label('Attendance Date')
                            ->date(),

                        TextEntry::make('schedule_timein')
                            ->label('Scheduled Time In')
                            ->dateTime('F d Y, h:i:s A'),

                        TextEntry::make('schedule_timeout')
                            ->label('Scheduled Time Out')
                            ->dateTime('F d Y, h:i:s A'),

                        TextEntry::make('actual_timein')
                            ->label('Actual Time In')
                            ->dateTime('F d Y, h:i:s A'),

                        TextEntry::make('actual_timeout')
                            ->label('Actual Time Out')
                            ->dateTime('F d Y, h:i:s A'),
                    ])
                    ->columns(3),
            ]);
    }
}
