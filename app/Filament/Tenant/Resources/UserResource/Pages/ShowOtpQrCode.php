<?php

namespace App\Filament\Tenant\Resources\UserResource\Pages;

use App\Models\User;
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
            // Generate QR code from the stored secret
            $totp = \OTPHP\TOTP::createFromSecret($record->otp_secret);
            $totp->setLabel(config('app.name', 'QuickJuan') . ' (' . $record->email . ')');
            $totp->setIssuer(config('app.name', 'QuickJuan'));

            $qrCodeString = $totp->getProvisioningUri();
            $qrCode = new \Endroid\QrCode\QrCode($qrCodeString);
            $writer = new \Endroid\QrCode\Writer\PngWriter();
            $result = $writer->write($qrCode);
            $this->qrCode = 'data:image/png;base64,' . base64_encode($result->getString());
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
