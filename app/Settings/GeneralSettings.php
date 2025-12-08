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


    public static function group(): string
    {
        return 'general';
    }
}
