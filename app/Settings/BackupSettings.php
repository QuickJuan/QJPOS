<?php

namespace App\Settings;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelSettings\Settings;

class BackupSettings extends Settings
{
    /** 'local' | 's3' | 'do-spaces' */
    public string $disk_driver = 'local';

    /** Absolute or relative path for local backups. Relative paths are resolved from base_path(). */
    public ?string $local_path = null; // null = default storage/app/asset-backups

    public ?string $s3_key       = null;
    public ?string $s3_secret    = null;
    public ?string $s3_region    = null;
    public ?string $s3_bucket    = null;
    public ?string $s3_endpoint  = null; // Used for DO Spaces, e.g. https://nyc3.digitaloceanspaces.com
    public ?string $s3_prefix    = 'asset-backups'; // Folder/path within the bucket

    public static function group(): string
    {
        return 'backup';
    }

    /**
     * Resolve the configured backup filesystem disk at runtime.
     * For local: returns the pre-configured 'asset-backups' disk.
     * For S3/DO Spaces: builds the disk on the fly from stored credentials.
     */
    /**
     * The effective local directory path that will be used when driver is 'local'.
     */
    public function resolvedLocalPath(): string
    {
        if (filled($this->local_path)) {
            return str_starts_with($this->local_path, '/')
                ? $this->local_path
                : base_path($this->local_path);
        }

        // Fall back to env var, then hardcoded default
        $envPath = env('ASSET_BACKUP_PATH');
        return filled($envPath) ? $envPath : storage_path('app/asset-backups');
    }

    /**
     * Human-readable label for the current backup destination.
     */
    public function destinationLabel(): string
    {
        return match ($this->disk_driver) {
            's3'        => 'AWS S3 — bucket: ' . ($this->s3_bucket ?: '(not set)') . ' / path: ' . ($this->s3_prefix ?: '/'),
            'do-spaces' => 'DigitalOcean Spaces — bucket: ' . ($this->s3_bucket ?: '(not set)') . ' / path: ' . ($this->s3_prefix ?: '/'),
            default     => $this->resolvedLocalPath(),
        };
    }

    public function toDisk(): Filesystem
    {
        if ($this->disk_driver === 'local') {
            return Storage::build([
                'driver' => 'local',
                'root'   => $this->resolvedLocalPath(),
                'throw'  => false,
            ]);
        }

        return Storage::build([
            'driver'                  => 's3',
            'key'                     => $this->s3_key,
            'secret'                  => $this->s3_secret,
            'region'                  => $this->s3_region ?? 'us-east-1',
            'bucket'                  => $this->s3_bucket,
            'endpoint'                => filled($this->s3_endpoint) ? $this->s3_endpoint : null,
            'use_path_style_endpoint' => false,
            'root'                    => filled($this->s3_prefix) ? $this->s3_prefix : 'asset-backups',
            'throw'                   => false,
        ]);
    }
}
