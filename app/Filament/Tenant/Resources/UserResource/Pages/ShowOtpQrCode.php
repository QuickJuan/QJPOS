<?php

namespace App\Filament\Tenant\Resources\UserResource\Pages;

use App\Models\User;
use App\Services\OtpSecretService;
use Filament\Resources\Pages\Page;
use Filament\Actions\Action;

class ShowOtpQrCode extends Page
{
    protected static string $resource = \App\Filament\Tenant\Resources\UserResource::class;

    protected static string $view = 'filament.pages.show-otp-qr-code';

    public User $record;

    public string $qrCode = '';
    public string $secret = '';

    public function mount(User $record): void
    {
        $this->record = $record;

        if ($record->otp_enabled && $record->otp_secret) {
            $this->secret = $record->otp_secret;
            // Generate QR code from existing secret
            $otpData = OtpSecretService::generateSecret($record->email, config('app.name', 'QuickJuan'));
            $this->qrCode = $otpData['qr_code'];
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back')
                ->url($this->getResource()::getUrl('index'))
                ->button(),
        ];
    }
}
