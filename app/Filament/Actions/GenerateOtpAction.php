<?php

namespace App\Filament\Actions;

use App\Models\User;
use App\Services\OtpSecretService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class GenerateOtpAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'generateOtp';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Generate OTP')
            ->icon('heroicon-m-key')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Generate OTP Secret')
            ->modalDescription('This will generate a new time-based OTP secret for this user. The user will need to scan the QR code with their authenticator app.')
            ->modalSubmitActionLabel('Generate')
            ->action(function () {
                $user = $this->getRecord();

                // Generate the secret and QR code
                $otpData = OtpSecretService::generateSecret($user->email, config('app.name', 'QuickJuan'));

                // Save to user
                $user->update([
                    'otp_secret' => $otpData['secret'],
                    'otp_enabled' => true,
                    'otp_enabled_at' => now(),
                ]);

                // Show QR code in a modal
                $this->successNotification()
                    ->title('OTP Secret Generated!')
                    ->body("The user should scan this QR code with their authenticator app.\n\nSecret: {$otpData['secret']}")
                    ->send();

                $this->getForm()->model()->refresh();
            });
    }
}
