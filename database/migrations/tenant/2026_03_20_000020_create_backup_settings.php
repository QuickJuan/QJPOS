<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('backup.disk_driver', 'local');
        $this->migrator->add('backup.s3_key', null);
        $this->migrator->add('backup.s3_secret', null);
        $this->migrator->add('backup.s3_region', null);
        $this->migrator->add('backup.s3_bucket', null);
        $this->migrator->add('backup.s3_endpoint', null);
        $this->migrator->add('backup.s3_prefix', 'asset-backups');
    }

    public function down(): void
    {
        $this->migrator->delete('backup.disk_driver');
        $this->migrator->delete('backup.s3_key');
        $this->migrator->delete('backup.s3_secret');
        $this->migrator->delete('backup.s3_region');
        $this->migrator->delete('backup.s3_bucket');
        $this->migrator->delete('backup.s3_endpoint');
        $this->migrator->delete('backup.s3_prefix');
    }
};
