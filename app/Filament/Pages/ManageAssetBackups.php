<?php

namespace App\Filament\Pages;

use App\Settings\BackupSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Number;
use ZipArchive;

class ManageAssetBackups extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-archive-box-arrow-down';
    protected static ?string $navigationLabel = 'Asset Backups';
    protected static ?string $navigationGroup = 'Central Management';
    protected static ?int    $navigationSort  = 5;

    protected static string $view = 'filament.pages.manage-asset-backups';

    public function getTitle(): string
    {
        return 'Asset Backups';
    }

    // ── Header actions ────────────────────────────────────────────────────
    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_backup')
                ->label('Create Backup Now')
                ->icon('heroicon-o-archive-box-arrow-down')
                ->color('primary')
                ->modalHeading('Create Asset Backup')
                ->modalSubmitActionLabel('Create Backup')
                ->form(function () {
                    $settings = app(BackupSettings::class);
                    $isLocal  = $settings->disk_driver === 'local';

                    $fields = [
                        Placeholder::make('destination_info')
                            ->label('Backup Destination')
                            ->content(new HtmlString(
                                '<span class="font-mono text-sm text-gray-700 dark:text-gray-300">' .
                                e($settings->destinationLabel()) .
                                '</span>'
                            ))
                            ->helperText(match ($settings->disk_driver) {
                                's3'        => 'Files will be uploaded to your AWS S3 bucket.',
                                'do-spaces' => 'Files will be uploaded to your DigitalOcean Spaces bucket.',
                                default     => 'Files will be saved to this directory on the server.',
                            }),
                    ];

                    if ($isLocal) {
                        $fields[] = TextInput::make('override_path')
                            ->label('Override Path (optional)')
                            ->placeholder($settings->resolvedLocalPath())
                            ->helperText('Leave blank to use the destination above. Enter an absolute path to save this backup to a different location, e.g. /mnt/nas/backups.')
                            ->maxLength(500)
                            ->suffixIcon('heroicon-m-folder');
                    }

                    return $fields;
                })
                ->action(function (array $data) {
                    $settings = app(BackupSettings::class);
                    $overridePath = $data['override_path'] ?? null;

                    // Temporarily apply override path for this backup run
                    if ($settings->disk_driver === 'local' && filled($overridePath)) {
                        $previousPath = $settings->local_path;
                        $settings->local_path = $overridePath;
                        $settings->save();
                    }

                    try {
                        Artisan::call('assets:backup');
                        Notification::make()
                            ->title('Backup created successfully')
                            ->body('Saved to: ' . $settings->destinationLabel())
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Backup failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    } finally {
                        // Restore original path if we temporarily overrode it
                        if (isset($previousPath)) {
                            $settings->local_path = $previousPath;
                            $settings->save();
                        }
                    }
                }),

            Action::make('restore_from_path')
                ->label('Restore from Server Path')
                ->icon('heroicon-o-server')
                ->color('gray')
                ->modalHeading('Restore from Server Path')
                ->form(function () {
                    $disk    = $this->backupDisk();
                    $options = collect($disk->files())
                        ->filter(fn ($f) => str_ends_with($f, '.zip') && ! str_starts_with($f, 'uploads/'))
                        ->sortByDesc(fn ($f) => $disk->lastModified($f))
                        ->mapWithKeys(fn ($f) => [$f => basename($f)])
                        ->toArray();

                    return [
                        \Filament\Forms\Components\Select::make('browse_file')
                            ->label('Browse backup storage')
                            ->options($options)
                            ->searchable()
                            ->placeholder('Select a file from the backup disk…')
                            ->helperText('Files available in the configured backup storage. Select one, or leave blank and enter a custom path below.')
                            ->live(),

                        TextInput::make('server_path')
                            ->label('Custom server path')
                            ->placeholder('/mnt/nas/backups/assets-abc-2026-03-20.zip')
                            ->helperText('Absolute path to a ZIP already on the server. Used when the file is outside the backup storage location (e.g. copied via SFTP).')
                            ->maxLength(500)
                            ->suffixIcon('heroicon-m-folder'),
                    ];
                })
                ->action(function (array $data) {
                    $browseFile = $data['browse_file'] ?? null;
                    $serverPath = trim($data['server_path'] ?? '');

                    if (filled($browseFile)) {
                        $this->restoreFromDisk($browseFile);
                    } elseif (filled($serverPath)) {
                        $this->restoreFromServerPath($serverPath);
                    } else {
                        Notification::make()
                            ->title('No file selected')
                            ->body('Please select a file from the list or enter a custom server path.')
                            ->warning()
                            ->send();
                    }
                }),

            Action::make('restore_backup')
                ->label('Restore from Upload')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->form([
                    FileUpload::make('backup_file')
                        ->label('Upload Backup ZIP')
                        ->acceptedFileTypes(['application/zip', 'application/x-zip-compressed'])
                        ->required()
                        ->disk('asset-backups')
                        ->directory('uploads'),
                ])
                ->action(function (array $data) {
                    $filename = $data['backup_file'];
                    $disk     = Storage::disk('asset-backups');

                    if (! $disk->exists($filename)) {
                        Notification::make()->title('File not found')->danger()->send();
                        return;
                    }

                    $this->extractZip($disk->path($filename));
                    $disk->delete($filename);
                }),
        ];
    }

    private function backupDisk(): \Illuminate\Contracts\Filesystem\Filesystem
    {
        return app(BackupSettings::class)->toDisk();
    }

    /**
     * Read backup-manifest.json from inside a zip on the backup disk.
     * Returns null for legacy backups that don't contain a manifest.
     */
    private function readManifest(string $zipPath): ?array
    {
        // For local disk we can open directly; for S3 we need a temp copy
        $localPath = $zipPath;
        $tempCreated = false;

        if (! file_exists($localPath)) {
            // Pull from remote disk to a temp file
            $content = $this->backupDisk()->get(basename($zipPath));
            if (! $content) {
                return null;
            }
            $localPath = sys_get_temp_dir() . '/' . uniqid('manifest_', true) . '.zip';
            file_put_contents($localPath, $content);
            $tempCreated = true;
        }

        $zip = new ZipArchive();
        if ($zip->open($localPath) !== true) {
            if ($tempCreated) {
                @unlink($localPath);
            }
            return null;
        }

        $json = $zip->getFromName('backup-manifest.json');
        $zip->close();

        if ($tempCreated) {
            @unlink($localPath);
        }

        return $json ? json_decode($json, true) : null;
    }

    // ── Table data ────────────────────────────────────────────────────────
    public function getBackups(): array
    {
        $disk         = $this->backupDisk();
        $currentTenant = app()->bound('tenant') && tenant() ? tenant('id') : null;

        $files = collect($disk->files())
            ->filter(fn ($f) => str_ends_with($f, '.zip') && ! str_starts_with($f, 'uploads/'))
            ->map(function ($file) use ($disk, $currentTenant) {
                // Extract tenant ID from filename: assets-{tenant_id}-{timestamp}.zip
                $name = basename($file);
                preg_match('/^assets-([a-f0-9\-]+)-\d{4}/', $name, $m);
                $tenantIdInFilename = $m[1] ?? null;

                $tenantLabel = match(true) {
                    $tenantIdInFilename === null                   => 'Unknown (legacy backup)',
                    $tenantIdInFilename === 'shared'               => 'Shared / outside tenant context',
                    $tenantIdInFilename === $currentTenant         => 'This tenant (' . $tenantIdInFilename . ')',
                    default                                        => 'Different tenant: ' . $tenantIdInFilename,
                };

                // On central panel (no tenant context) treat everything as visible
                $isMine = $currentTenant === null || $tenantIdInFilename === $currentTenant;

                return [
                    'name'       => $name,
                    'path'       => $file,
                    'size'       => Number::fileSize($disk->size($file), precision: 1),
                    'created_at' => \Carbon\Carbon::createFromTimestamp($disk->lastModified($file))->format('M j, Y g:i A'),
                    'tenant'     => $tenantLabel,
                    'is_mine'    => $isMine,
                ];
            })
            ->sortByDesc('created_at')
            ->values()
            ->toArray();

        return $files;
    }

    // ── Per-file actions ──────────────────────────────────────────────────
    public function downloadBackup(string $path): mixed
    {
        $disk = $this->backupDisk();

        if (! $disk->exists($path)) {
            Notification::make()->title('File not found')->danger()->send();
            return null;
        }

        return response()->streamDownload(function () use ($disk, $path) {
            echo $disk->get($path);
        }, basename($path), ['Content-Type' => 'application/zip']);
    }

    public function deleteBackup(string $path): void
    {
        $this->backupDisk()->delete($path);

        Notification::make()
            ->title('Backup deleted')
            ->success()
            ->send();
    }

    /**
     * Restore directly from a zip already on the backup disk (no upload needed).
     * Called via wire:click from the row Restore button.
     */
    public function restoreFromDisk(string $path): void
    {
        $disk = $this->backupDisk();

        if (! $disk->exists($path)) {
            Notification::make()->title('File not found on backup disk')->danger()->send();
            return;
        }

        // Stream from disk to a local temp file so ZipArchive can open it
        $tempPath = sys_get_temp_dir() . '/' . uniqid('restore_', true) . '.zip';
        file_put_contents($tempPath, $disk->get($path));

        $this->extractZip($tempPath);

        @unlink($tempPath);
    }

    /**
     * Restore from an absolute path already on the server (no disk involvement).
     * Called from the "Restore from Server Path" header action.
     */
    public function restoreFromServerPath(string $absolutePath): void
    {
        if (! file_exists($absolutePath)) {
            Notification::make()
                ->title('File not found')
                ->body("No file exists at: {$absolutePath}")
                ->danger()
                ->send();
            return;
        }

        $this->extractZip($absolutePath);
    }

    /**
     * Core extraction logic shared by all restore methods.
     */
    private function extractZip(string $localZipPath): void
    {
        $zip = new ZipArchive();

        if ($zip->open($localZipPath) !== true) {
            Notification::make()->title('Could not open ZIP file')->danger()->send();
            return;
        }

        // Tenant safety check
        $currentTenant = app()->bound('tenant') && tenant() ? tenant('id') : null;
        $manifestJson  = $zip->getFromName('backup-manifest.json');
        $manifest      = $manifestJson ? json_decode($manifestJson, true) : null;

        if ($manifest && isset($manifest['tenant_id']) && $manifest['tenant_id'] !== null) {
            $backupTenant = $manifest['tenant_id'];
            if ($backupTenant !== $currentTenant) {
                $zip->close();
                Notification::make()
                    ->title('Tenant mismatch — restore aborted')
                    ->body("This backup belongs to tenant [{$backupTenant}] but current tenant is [{$currentTenant}]. Download and restore manually if migrating between tenants.")
                    ->danger()
                    ->persistent()
                    ->send();
                return;
            }
        }

        $destinations = [
            'storage'       => storage_path('app/public'),
            'public/images' => base_path('public/images'),
            'public/videos' => base_path('public/videos'),
        ];

        $count = 0;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->getNameIndex($i);
            if (str_ends_with($entry, '/') || $entry === 'backup-manifest.json') {
                continue;
            }

            $destPath = null;
            foreach ($destinations as $prefix => $basePath) {
                if (str_starts_with($entry, $prefix . '/')) {
                    $relative = substr($entry, strlen($prefix) + 1);
                    $destPath = $basePath . DIRECTORY_SEPARATOR . $relative;
                    break;
                }
            }

            // Fallback: legacy backups without prefix go to storage/app/public
            if ($destPath === null) {
                $destPath = storage_path('app/public') . DIRECTORY_SEPARATOR . $entry;
            }

            @mkdir(dirname($destPath), 0755, true);
            file_put_contents($destPath, $zip->getFromIndex($i));
            $count++;
        }

        $zip->close();

        Notification::make()
            ->title('Assets restored successfully')
            ->body("{$count} files restored to storage, public/images, and public/videos.")
            ->success()
            ->send();
    }
}
