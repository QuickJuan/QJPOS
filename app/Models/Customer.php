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
        'uuid',
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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($customer) {
            if (empty($customer->uuid)) {
                $customer->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });

        // Create e-wallet when customer is created
        static::created(function ($customer) {
            $customer->eWallet()->create([
                'balance' => 0,
                'total_loaded' => 0,
                'total_spent' => 0,
                'earned_points' => $customer->earned_points ?? 0,
                'redeemed_points' => $customer->redeemed_points ?? 0,
                'points_balance' => $customer->balance ?? 0,
                'is_active' => true,
            ]);
        });
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function eWallet(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CustomerEWallet::class);
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
