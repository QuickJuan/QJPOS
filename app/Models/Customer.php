<?php

namespace App\Models;

use App\Enums\CustomerType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Customer extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'customer_name',
        'birth_date',
        'contact_no',
        'email',
        'type',
        'last_visit',
        'email_subscribe',
        'sms_subscribe',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'last_visit' => 'datetime',
        'email_subscribe' => 'boolean',
        'sms_subscribe' => 'boolean',
        'type' => CustomerType::class,
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile')
            ->singleFile()
            ->useFallbackUrl('/images/default-profile.png');
    }
}
