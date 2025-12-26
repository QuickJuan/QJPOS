<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;

class OtpVerificationService
{
    private const DEFAULT_WINDOW_SECONDS = 120;

    public function __construct(
        protected TwoFactorAuthenticationProvider $provider
    ) {
    }

    /**
     * Ensure the provided one-time password is valid for the given user.
     */
    public function verify(User $user, string $code, ?int $windowSeconds = null): void
    {
        if (empty($user->two_factor_secret) || empty($user->two_factor_confirmed_at)) {
            throw ValidationException::withMessages([
                'otp_code' => __('Two-factor authentication is not enabled for this user.'),
            ]);
        }

        try {
            $secret = decrypt($user->two_factor_secret);
        } catch (\Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'otp_code' => __('Unable to verify one-time password. Please regenerate your authenticator setup.'),
            ]);
        }

        $normalizedCode = Str::of($code)
            ->replaceMatches('/\s+/', '')
            ->__toString();

        $window = (int) ceil(($windowSeconds ?? self::DEFAULT_WINDOW_SECONDS) / 30);
        $window = $window > 0 ? $window : 1;

        $isValid = $this->provider->verify($secret, $normalizedCode, $window);

        if (! $isValid) {
            throw ValidationException::withMessages([
                'otp_code' => __('The provided one-time password is invalid or has expired.'),
            ]);
        }
    }
}
