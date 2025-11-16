<?php

namespace App\Filament\Actions;

use App\Mail\TenantApplicationReceived;
use App\Models\Central\TenantApplication;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Mail;
use Filament\Notifications\Notification;

class ResendTenantApplicationEmail extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'resend_email';
    }

    public static function make(?string $name = null): static
    {
        $name ??= static::getDefaultName();
        $action = parent::make($name);
        $action
            ->label('Resend Email')
            ->icon('heroicon-o-envelope')
            ->color('info')
            ->action(static function (TenantApplication $record): void {
                try {
                    Mail::to($record->owner_email)->send(
                        new TenantApplicationReceived($record)
                    );

                    Notification::make()
                        ->title('Success')
                        ->body('Confirmation email sent successfully to ' . $record->owner_email)
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Error')
                        ->body('Failed to send email: ' . $e->getMessage())
                        ->danger()
                        ->send();
                }
            });

        return $action;
    }
}
