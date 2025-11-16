<?php

namespace App\Listeners;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Stancl\Tenancy\Events\TenantCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\File;
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
        try {
            $tenant = $event->tenant;            // Get admin info from tenant's data payload
            $adminName = $tenant->name ?? 'Admin';
            $adminEmail = $tenant->email ?? 'admin@tenant.test';

            // Switch into tenant context
            tenancy()->initialize($tenant);

            // Create the default admin user
            User::create([
                'name' => $adminName,
                'email' => $adminEmail,
                'password' => Hash::make('password'), // Default password or randomized
            ]);

            // Create default Main Branch
            Branch::create([
                'branch_code' => 'MAIN',
                'name' => 'Main Branch',
                'address' => $tenant->address ?? '',
                'phone' => $tenant->phone ?? '',
                'email' => $tenant->email ?? '',
                'is_active' => true,
            ]);

            $tenantId = $tenant->id;

            $tenant->run(function ($tenant) {
                $storagePath = storage_path();

                $directories = [
                    "$storagePath/app",
                    "$storagePath/app/public",
                    "$storagePath/framework",
                    "$storagePath/framework/sessions",
                    "$storagePath/framework/testing",
                    "$storagePath/framework/views",
                    "$storagePath/framework/cache",
                    "$storagePath/logs",
                ];

                foreach ($directories as $directory) {
                    if (! is_dir($directory)) {
                        @mkdir($directory, 0777, true);
                    }
                }
            });

            // Switch back to central context
            tenancy()->end();
        } catch (\Exception $e) {
            // Log the error but don't throw to prevent breaking Filament responses
            info('Error in CreateDefaultTenantUser listener: ' . $e->getMessage());
        }
    }
}
