<?php

namespace App\Models\Central;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

     protected $fillable = [
        'tenancy_db_name',
        'name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'billing_type',
        'subscription',
        'subscription_status',
        'subscription_ends_at',
    ];

     protected $casts = [
        'subcription_ends_at' => 'datetime',
    ];


    public static function getCustomColumns(): array
    {
        return [
            'id',
            'tenancy_db_name',
            'name',
            'email',
            'phone',
            'address',
            'city',
            'state',
            'country',
            'billing_type',
            'subscription',
            'subscription_status',
            'subscription_ends_at',
        ];
    }

    public function getDatabaseName(): string
    {
        return $this->tenancy_db_name;
    }


    public function domains()
    {
        return $this->hasMany(Domain::class);
    }
}