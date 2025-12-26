<?php

namespace App\Services;

use OTPHP\TOTP;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class OtpSecretService
{
    /**
     * Generate a new OTP secret and QR code for a user
     */
    public static function generateSecret(string $userEmail, string $companyName = 'QuickJuan'): array
    {
        // Create a new TOTP instance
        $secret = TOTP::create();

        // Set the label (user@company format)
        $label = "{$companyName} ({$userEmail})";
        $secret->setLabel($label);
        $secret->setIssuer($companyName);

        // Generate the provisioning URI for QR code
        $qrCodeString = $secret->getProvisioningUri();

        // Generate QR code
        $qrCode = new QrCode($qrCodeString);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        $qrCodeImage = 'data:image/png;base64,' . base64_encode($result->getString());

        return [
            'secret' => $secret->getSecret(),
            'qr_code' => $qrCodeImage,
            'provisioning_uri' => $qrCodeString,
        ];
    }

    /**
     * Verify an OTP code against a secret
     */
    public static function verifyCode(string $secret, string $code, int $window = 1): bool
    {
        try {
            $totp = TOTP::createFromSecret($secret);
            return $totp->verify($code, null, $window);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get the current OTP code (for testing/display)
     */
    public static function getCurrentCode(string $secret): string
    {
        try {
            $totp = TOTP::createFromSecret($secret);
            return $totp->now();
        } catch (\Exception $e) {
            return '';
        }
    }
}
