<?php

namespace App\MediaLibrary;

use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;

class TenantAwareUrlGenerator extends DefaultUrlGenerator
{
    public function getUrl(): string
    {
        if (isCentralDomain()) {
            $url = asset($this->getPathRelativeToRoot());
        } else {
            $url = tenant_asset($this->getPathRelativeToRoot());
        }

        $url = $this->versionUrl($url);

        return $url;
    }
}
