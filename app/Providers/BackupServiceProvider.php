<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Backup\BackupServiceProvider as SpatieBackupServiceProvider;

class BackupServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Override backup config for multi-tenancy
        $this->app->extend('config', function ($config) {
            $backupConfig = $config->get('backup');

            // Make name dynamic
            if (is_callable($backupConfig['backup']['name'])) {
                $backupConfig['backup']['name'] = $backupConfig['backup']['name']();
            }

            // Make databases dynamic
            if (is_callable($backupConfig['backup']['source']['databases'])) {
                $backupConfig['backup']['source']['databases'] = $backupConfig['backup']['source']['databases']();
            }

            // Make disks dynamic
            if (is_callable($backupConfig['backup']['destination']['disks'])) {
                $backupConfig['backup']['destination']['disks'] = $backupConfig['backup']['destination']['disks']();
            }

            // Make filename prefix dynamic
            if (is_callable($backupConfig['backup']['destination']['filename_prefix'])) {
                $backupConfig['backup']['destination']['filename_prefix'] = $backupConfig['backup']['destination']['filename_prefix']();
            }

            $config->set('backup', $backupConfig);            return $config;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Ensure backup directories exist
        $centralBackupPath = storage_path('app/central-backups');
        if (!file_exists($centralBackupPath)) {
            mkdir($centralBackupPath, 0755, true);
        }

        // Ensure tenant-specific backup directories exist
        if (app()->bound('tenant') && tenant()) {
            $tenantBackupPath = storage_path('app/tenant-' . tenant('id') . '/backups');
            if (!file_exists($tenantBackupPath)) {
                mkdir($tenantBackupPath, 0755, true);
            }
        }
    }
}
