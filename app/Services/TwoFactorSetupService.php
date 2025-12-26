<?php

namespace App\Services;

use App\Models\User;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;

class TwoFactorSetupService
{
    public function __construct(
        protected TwoFactorAuthenticationProvider $provider
    ) {
    }

    /**
     * Regenerate the two-factor credentials for the provided user and return rendered assets.
     */
    public function regenerate(User $user): array
    {
        $secretKey = $this->provider->generateSecretKey();

        $user->forceFill([
            'two_factor_secret'         => encrypt($secretKey),
            'two_factor_recovery_codes' => encrypt(json_encode($this->generateRecoveryCodes())),
            'two_factor_confirmed_at'   => now(),
        ])->save();

        return $this->buildPayload($user, $secretKey);
    }

    /**
     * Return the current two-factor assets, regenerating credentials if missing.
     */
    public function generate(User $user): array
    {
        if (empty($user->two_factor_secret) || empty($user->two_factor_recovery_codes)) {
            return $this->regenerate($user);
        }

        try {
            $secret = decrypt($user->two_factor_secret);
        } catch (\Throwable $exception) {
            report($exception);

            return $this->regenerate($user);
        }

        return $this->buildPayload($user, $secret);
    }

    protected function buildPayload(User $user, string $secret): array
    {
        return [
            'svg'            => $this->generateQrSvg($user, $secret),
            'secret'         => $secret,
            'recoveryCodes'  => $this->getRecoveryCodes($user),
            'application'    => config('app.name'),
            'email'          => $user->email,
        ];
    }

    protected function generateQrSvg(User $user, string $secret): string
    {
        $uri = $this->provider->qrCodeUrl(config('app.name'), $user->email, $secret);

        $writer = new Writer(
            new ImageRenderer(
                new RendererStyle(256),
                new SvgImageBackEnd()
            )
        );

        return $writer->writeString($uri);
    }

    protected function generateRecoveryCodes(int $count = 8): array
    {
        return collect()->times($count, function () {
            return Str::upper(Str::random(4)) . '-' . Str::upper(Str::random(4));
        })->all();
    }

    protected function getRecoveryCodes(User $user): array
    {
        if (blank($user->two_factor_recovery_codes)) {
            return [];
        }

        try {
            $codes = json_decode(decrypt($user->two_factor_recovery_codes), true);
        } catch (\Throwable $exception) {
            report($exception);

            return [];
        }

        return Arr::wrap($codes);
    }
}
