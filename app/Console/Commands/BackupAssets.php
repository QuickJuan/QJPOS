<?php

namespace App\Console\Commands;

use App\Settings\BackupSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class BackupAssets extends Command
{
    protected $signature = 'assets:backup {--name= : Optional backup file name}';
    protected $description = 'Create a zip backup of all uploaded assets (storage/app/public, public/images, public/videos)';

    /**
     * Directories to back up, keyed by the zip prefix used during restore.
     * Prefix => absolute path on disk.
     */
    private function sourceDirs(): array
    {
        return [
            'storage' => storage_path('app/public'),
            'public/images' => base_path('public/images'),
            'public/videos' => base_path('public/videos'),
        ];
    }

    public function handle(): int
    {
        // Resolve tenant context (null when run outside tenant scope)
        $tenantId  = app()->bound('tenant') && tenant() ? tenant('id') : null;
        $tenantKey = $tenantId ?? 'shared';

        $name     = $this->option('name') ?: "assets-{$tenantKey}-" . now()->format('Y-m-d_H-i-s') . '.zip';
        $settings = app(BackupSettings::class);
        $disk     = $settings->toDisk();
        $this->line("Storage driver: [{$settings->disk_driver}]");
        $this->line("Tenant context: [{$tenantKey}]");
        $tempPath = storage_path('app/backup-temp/' . $name);

        @mkdir(dirname($tempPath), 0755, true);

        $zip = new ZipArchive();
        if ($zip->open($tempPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error("Could not create zip file: {$tempPath}");
            return self::FAILURE;
        }

        $count = 0;
        foreach ($this->sourceDirs() as $prefix => $sourceDir) {
            if (! is_dir($sourceDir)) {
                $this->warn("Skipping (not found): {$sourceDir}");
                continue;
            }

            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($sourceDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $file) {
                if ($file->isFile()) {
                    $relativePath = str_replace($sourceDir . DIRECTORY_SEPARATOR, '', $file->getRealPath());
                    $zip->addFile($file->getRealPath(), $prefix . '/' . $relativePath);
                    $count++;
                }
            }
        }

        // Embed a manifest so this backup can be mapped to the correct tenant on restore
        $manifest = json_encode([
            'tenant_id'    => $tenantId,
            'tenant_key'   => $tenantKey,
            'created_at'   => now()->toIso8601String(),
            'file_count'   => $count,
            'includes'     => array_keys($this->sourceDirs()),
            'disk_driver'  => $settings->disk_driver,
            'note'         => $tenantId
                ? "Tenant-specific backup. storage/ contains files for tenant {$tenantId} only."
                : "Shared backup created outside tenant context. storage/ contains files from storage/app/public.",
        ], JSON_PRETTY_PRINT);
        $zip->addFromString('backup-manifest.json', $manifest);

        $zip->close();

        // Upload to configured disk (streaming to avoid loading large files into memory)
        $disk->put($name, fopen($tempPath, 'rb'));
        @unlink($tempPath);

        $this->info("Asset backup created: {$name} ({$count} files, tenant: {$tenantKey})");

        return self::SUCCESS;
    }
}
