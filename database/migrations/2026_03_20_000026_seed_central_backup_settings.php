<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Seeds default backup settings into the central database settings table.
 * These are the same defaults as those seeded into each tenant DB via
 * database/migrations/tenant/2026_03_20_000020_create_backup_settings.php.
 */
return new class extends Migration
{
    private array $rows = [
        ['name' => 'disk_driver', 'payload' => '"local"'],
        ['name' => 'local_path',  'payload' => 'null'],
        ['name' => 's3_key',      'payload' => 'null'],
        ['name' => 's3_secret',   'payload' => 'null'],
        ['name' => 's3_region',   'payload' => 'null'],
        ['name' => 's3_bucket',   'payload' => 'null'],
        ['name' => 's3_endpoint', 'payload' => 'null'],
        ['name' => 's3_prefix',   'payload' => '"asset-backups"'],
    ];

    public function up(): void
    {
        foreach ($this->rows as $row) {
            DB::table('settings')->updateOrInsert(
                ['group' => 'backup', 'name' => $row['name']],
                ['group' => 'backup', 'name' => $row['name'], 'locked' => false, 'payload' => $row['payload'], 'created_at' => now(), 'updated_at' => now()],
            );
        }
    }

    public function down(): void
    {
        DB::table('settings')->where('group', 'backup')->delete();
    }
};
