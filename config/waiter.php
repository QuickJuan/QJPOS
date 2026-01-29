<?php

return [
    // Allowed values: 'otp' (default) or 'pincode'. Controls which credential the waiter login screen expects.
    'login_method' => env('WAITER_LOGIN_METHOD', 'otp'),

    // Optional overrides for code length if needed.
    'otp_length' => env('WAITER_OTP_LENGTH', 6),
    'pincode_length' => env('WAITER_PINCODE_LENGTH', 4),
];
