<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Queue\InteractsWithQueue;
use Stancl\Tenancy\Events\TenantCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\StanclTenancyEventsTenantCreated;

class CreateDefaultTenantUser
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TenantCreated $event): void
    {
        $tenant = $event->tenant;

        // Get admin info from tenant's data payload
        $adminName = $tenant->name ?? 'Admin';
        $adminEmail = $tenant->email ?? 'admin@tenant.test';

        info('Creating default admin user for tenant: ' . $tenant->name);

        // Switch into tenant context
        tenancy()->initialize($tenant);

        // Create the default admin user
        User::create([
            'name' => $adminName,
            'email' => $adminEmail,
            'password' => Hash::make('password'), // Default password or randomized
        ]);

        // Switch back to central context
        tenancy()->end();
    }
}
