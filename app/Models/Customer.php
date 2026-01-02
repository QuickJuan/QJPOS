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
        'earned_points',
        'redeemed_points',
        'balance',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'last_visit' => 'datetime',
        'email_subscribe' => 'boolean',
        'sms_subscribe' => 'boolean',
        'type' => CustomerType::class,
        'earned_points' => 'float',
        'redeemed_points' => 'float',
        'balance' => 'float',
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

    /**
     * Check if customer has sufficient points balance
     */
    public function hasSufficientPoints(float $requiredPoints): bool
    {
        return $this->balance >= $requiredPoints;
    }

    /**
     * Get formatted points balance
     */
    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->balance, 2);
    }
}
