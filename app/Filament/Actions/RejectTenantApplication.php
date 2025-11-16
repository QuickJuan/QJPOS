<?php

namespace App\Filament\Actions;

use App\Mail\TenantApplicationRejected;
use App\Models\Central\TenantApplication;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Mail;
use Filament\Notifications\Notification;

class RejectTenantApplication extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'reject_application';
    }

    public static function make(?string $name = null): static
    {
        $name ??= static::getDefaultName();
        $action = parent::make($name);
        $action
            ->label('Reject')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->form([
                Section::make('Rejection Reason')
                    ->description('Provide feedback for the applicant (optional)')
                    ->schema([
                        Textarea::make('rejection_reason')
                            ->label('Reason for Rejection')
                            ->helperText('This message will be sent to the applicant')
                            ->rows(4)
                            ->placeholder('Enter the reason why the application is being rejected...')
                            ->maxLength(1000),
                    ]),
            ])
            ->action(static function (TenantApplication $record, array $data): void {
                try {
                    // Update application status
                    $record->update([
                        'status' => 'rejected',
                        'notes' => $data['rejection_reason'] ?? 'Application rejected.',
                    ]);

                    // Send rejection email
                    Mail::to($record->owner_email)->send(
                        new TenantApplicationRejected(
                            $record,
                            $data['rejection_reason'] ?? ''
                        )
                    );

                    Notification::make()
                        ->title('Success')
                        ->body('Application rejected and notification email sent to ' . $record->owner_email)
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Error')
                        ->body('Failed to reject application: ' . $e->getMessage())
                        ->danger()
                        ->send();
                }
            });

        return $action;
    }
}
