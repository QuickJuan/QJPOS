<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeaveRequestSubmitted extends Notification
{
    use Queueable;

    public function __construct(public LeaveRequest $leaveRequest)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $employee  = $this->leaveRequest->employee?->user?->name
            ?? "Employee #{$this->leaveRequest->employee_id}";

        $leaveType = $this->leaveRequest->leaveType?->name ?? 'Leave';
        $days      = $this->leaveRequest->days_requested;
        $start     = $this->leaveRequest->start_date?->format('M d, Y');
        $end       = $this->leaveRequest->end_date?->format('M d, Y');
        $viewUrl   = '/admin/leave-requests/' . $this->leaveRequest->id . '/view';

        return FilamentNotification::make()
            ->title('New Leave Request')
            ->body("{$employee} filed a **{$leaveType}** request for **{$days} day(s)** ({$start} – {$end}).")
            ->icon('heroicon-o-calendar-days')
            ->iconColor('warning')
            ->actions([
                Action::make('view')
                    ->label('Review Request')
                    ->button()
                    ->url($viewUrl),
            ])
            ->getDatabaseMessage();
    }
}
