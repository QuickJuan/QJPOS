<x-filament-panels::page>

    {{-- Info banner --}}
    @php
        $backupSettings = app(\App\Settings\BackupSettings::class);
        $driverLabel = match($backupSettings->disk_driver) {
            's3'        => 'AWS S3 (bucket: ' . ($backupSettings->s3_bucket ?: 'not configured') . ')',
            'do-spaces' => 'DigitalOcean Spaces (bucket: ' . ($backupSettings->s3_bucket ?: 'not configured') . ')',
            default     => $backupSettings->resolvedLocalPath(),
        };
        $backups = $this->getBackups();
    @endphp

    <div class="rounded-xl border border-primary-200 bg-primary-50 p-4 text-sm text-primary-800 dark:border-primary-800 dark:bg-primary-950 dark:text-primary-300">
        <p class="font-semibold">What is backed up?</p>
        <p class="mt-1">
            Uploaded files from three locations:
            <code class="rounded bg-primary-100 px-1 dark:bg-primary-900">storage/app/public</code> (media library, receipts),
            <code class="rounded bg-primary-100 px-1 dark:bg-primary-900">public/images</code> (product &amp; page images), and
            <code class="rounded bg-primary-100 px-1 dark:bg-primary-900">public/videos</code> (landing page videos).
            Restoring a backup will overwrite existing files in those directories.
        </p>
        <p class="mt-2">
            <span class="font-medium">Storage destination:</span>
            <code class="rounded bg-primary-100 px-1 dark:bg-primary-900">{{ $driverLabel }}</code>
            &mdash;
            <a href="{{ \Filament\Facades\Filament::getPanel('central')->getUrl() . '/manage-backup-settings' }}"
               class="underline">Change &rarr;</a>
        </p>
    </div>

    {{-- Backups table --}}
    <div class="w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">

        {{-- Table header --}}
        <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-700">
            <h2 class="text-base font-semibold text-gray-800 dark:text-white">Existing Backups</h2>
            <span class="text-xs text-gray-400">{{ count($backups) }} {{ count($backups) === 1 ? 'file' : 'files' }}</span>
        </div>

        @if (count($backups) === 0)
            <div class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                No asset backups yet. Click <strong>Create Backup Now</strong> to generate one.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">File Name</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tenant</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 whitespace-nowrap">Size</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 whitespace-nowrap">Created</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach ($backups as $backup)
                            <tr class="group transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/60">

                                {{-- File name --}}
                                <td class="px-4 py-3">
                                    <span class="font-mono text-xs text-gray-800 dark:text-gray-200 break-all">{{ $backup['name'] }}</span>
                                </td>

                                {{-- Tenant --}}
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if ($backup['tenant'] === 'Shared / outside tenant context')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                            <x-heroicon-m-globe-alt class="h-3 w-3" /> Central
                                        </span>
                                    @elseif (str_starts_with($backup['tenant'], 'This tenant'))
                                        <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                            <x-heroicon-m-check-circle class="h-3 w-3" /> {{ $backup['tenant'] }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300" title="{{ $backup['tenant'] }}">
                                            {{ $backup['tenant'] }}
                                </td>

                                {{-- Size --}}
                                <td class="px-4 py-3 whitespace-nowrap text-gray-500 dark:text-gray-400">{{ $backup['size'] }}</td>

                                {{-- Created --}}
                                <td class="px-4 py-3 whitespace-nowrap text-gray-500 dark:text-gray-400">{{ $backup['created_at'] }}</td>

                                {{-- Actions --}}
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">

                                        {{-- Restore (server-side, no upload) --}}
                                        <button
                                            wire:click="restoreFromDisk('{{ $backup['path'] }}')"
                                            wire:confirm="Restore this backup? Existing files in storage/app/public, public/images, and public/videos will be overwritten."
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-green-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-green-700 disabled:opacity-50"
                                        >
                                            <x-heroicon-m-arrow-path class="h-3.5 w-3.5" />
                                            Restore
                                        </button>

                                        {{-- Download --}}
                                        <button
                                            wire:click="downloadBackup('{{ $backup['path'] }}')"
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 disabled:opacity-50"
                                        >
                                            <x-heroicon-m-arrow-down-tray class="h-3.5 w-3.5" />
                                            Download
                                        </button>

                                        {{-- Delete --}}
                                        <button
                                            wire:click="deleteBackup('{{ $backup['path'] }}')"
                                            wire:confirm="Delete this backup permanently? This cannot be undone."
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-red-700 disabled:opacity-50"
                                        >
                                            <x-heroicon-m-trash class="h-3.5 w-3.5" />
                                            Delete
                                        </button>

                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</x-filament-panels::page>
