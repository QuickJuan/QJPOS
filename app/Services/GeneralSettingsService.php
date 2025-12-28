<?php

namespace App\Services;

use App\Models\Central\Tenant;
use App\Models\GeneralSetting;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\Storage;

class GeneralSettingsService
{
    public function getCompanySettings(): array
    {
        $generalSettings = app(GeneralSettings::class);
        $companyLogo = $generalSettings->company_logo ? tenant_asset($generalSettings->company_logo) : null;
        $heroImage = $generalSettings->hero_image ? tenant_asset($generalSettings->hero_image) : null;

        return [
            'company_name'    => $generalSettings->company_name,
            'company_address' => $generalSettings->company_address,
            'company_phone'   => $generalSettings->company_contact,
            'company_logo'    => $companyLogo,
            'hero_image'      => $heroImage,
            'timezone'        => $generalSettings->timezone ?? 'UTC',
        ];
    }
}
