<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Stancl\Tenancy\Concerns\HasATenantArgument;
use Stancl\Tenancy\Concerns\TenantAwareCommand;
use Illuminate\Support\Facades\Artisan;

class TenantBackupCommand extends Command
{
    use TenantAwareCommand, HasATenantArgument;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:backup {--tenant=} {--all-tenants}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a backup for a specific tenant or all tenants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('all-tenants')) {
            $this->info('Creating backups for all tenants...');

            tenancy()->all()->each(function ($tenant) {
                $this->line("Creating backup for tenant: {$tenant->id}");

                tenancy()->initialize($tenant);

                $databaseName = config('database.connections.tenant.database', 'tenant_db');
                $this->line("  Database: {$databaseName}");

                try {
                    Artisan::call('backup:run', [
                        '--only-db' => true, // Only backup database for tenants
                        '--disable-notifications' => true,
                    ]);

                    $this->info("✅ Backup completed for tenant: {$tenant->id} (DB: {$databaseName})");
                } catch (\Exception $e) {
                    $this->error("❌ Backup failed for tenant {$tenant->id}: " . $e->getMessage());
                }

                tenancy()->end();
            });            return Command::SUCCESS;
        }

        // Single tenant backup
        return $this->handle();
    }

    /**
     * Handle backup for current tenant context
     */
    public function handleTenant()
    {
        $tenantId = tenant('id');
        $databaseName = config('database.connections.tenant.database', 'tenant_db');

        $this->info("Creating backup for tenant: {$tenantId}");
        $this->info("Database: {$databaseName}");

        try {
            Artisan::call('backup:run', [
                '--only-db' => true, // Only backup database for tenants
                '--disable-notifications' => true,
            ]);

            $output = Artisan::output();
            $this->line($output);

            $this->info("✅ Backup completed successfully!");
            $this->info("   Tenant: {$tenantId}");
            $this->info("   Database: {$databaseName}");
            $this->info("   File prefix: {$tenantId}_{$databaseName}_");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Backup failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
