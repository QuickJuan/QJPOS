<?php

namespace App\Listeners;

use App\Models\User;
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

        $tenantId = $tenant->id;

        $tenant->run(function ($tenant) {
            $storage_path = storage_path();

            mkdir("$storage_path/app",0777, true);
//            file_put_contents("$storage_path/app/.gitignore", "*\n!.gitignore");

            mkdir("$storage_path/app/public",0777, true);
//            file_put_contents("$storage_path/app/public/.gitignore", "*\n!.gitignore");

            mkdir("$storage_path/framework",0777, true);
//            file_put_contents("$storage_path/framework/.gitignore", "*\n!.gitignore");

            mkdir("$storage_path/framework/sessions", 0777, true);
//            file_put_contents("$storage_path/framework/sessions/.gitignore", "*\n!.gitignore");

            mkdir("$storage_path/framework/testing", 0777, true);
//            file_put_contents("$storage_path/framework/testing/.gitignore", "*\n!.gitignore");

            mkdir("$storage_path/framework/views", 0777, true);
//            file_put_contents("$storage_path/framework/views/.gitignore", "*\n!.gitignore");

            mkdir("$storage_path/framework/cache", 0777, true);
//            file_put_contents("$storage_path/framework/cache/.gitignore", "*\n!.gitignore");

            mkdir("$storage_path/logs", 0777, true);
//            file_put_contents("$storage_path/logs/.gitignore", "*\n!.gitignore");
        });


        // $tenantStoragePath = storage_path("tenant/{$event->tenant->id}");

        // // Ensure the folder exists
        // File::makeDirectory("{$tenantStoragePath}/app/public", 0777, true, true);
        // File::makeDirectory("{$tenantStoragePath}/framework/cache/facade", 0777, true, true);
        // File::makeDirectory("{$tenantStoragePath}/framework/views", 0775, true, true);
        // File::makeDirectory("{$tenantStoragePath}/framework/sessions", 0775, true, true);
        // File::makeDirectory("{$tenantStoragePath}/framework/testing", 0775, true, true);
        // File::makeDirectory("{$tenantStoragePath}/logs", 0775, true, true);

        // Switch back to central context
        tenancy()->end();
    }
}
