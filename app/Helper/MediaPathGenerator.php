<?php

namespace App\Helper;

use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Stancl\Tenancy\Facades\Tenancy;

class MediaPathGenerator extends DefaultPathGenerator
{
    public function getPath(Media $media): string
    {
        $tenantId = Tenancy::tenant()?->getTenantKey() ?? 'central';
        return "tenants/{$tenantId}/" . parent::getPath($media);
    }

    public function getPathForConversions(Media $media): string
    {
        $tenantId = Tenancy::tenant()?->getTenantKey() ?? 'central';
        return "tenants/{$tenantId}/" . parent::getPathForConversions($media);
    }
}
