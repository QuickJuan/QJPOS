<?php

namespace App\Settings;

use Spatie\MediaLibrary\HasMedia;
use Spatie\LaravelSettings\Settings;
use Spatie\MediaLibrary\InteractsWithMedia;

class GeneralSettings extends Settings
{
    // use InteractsWithMedia;

    public ?string $company_name;
    public ?string $company_address;
    public ?string $company_contact;
    public ?string $company_logo;
    public ?string $hero_image;
    public ?string $timezone = 'UTC';
    public float $points_earning_rate = 100; // Amount needed to earn 1 point
    public ?string $contact_recipient_emails = '';
    public bool $enable_feedback_qr_code = false;


    public static function group(): string
    {
        return 'general';
    }
}
