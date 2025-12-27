<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class TenantApplication extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'tenant_applications';

    protected $fillable = [
        'business_name',
        'business_address',
        'owner_name',
        'owner_email',
        'owner_phone',
        'business_permit_number',
        'status', // pending, approved, rejected
        'notes',
        'accept_terms',
        'accept_privacy',
        'accept_promotions',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Register media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile();
    }

    /**
     * Get the logo URL
     */
    public function logoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getFirstMediaUrl('logo') ?: null,
        );
    }
}
