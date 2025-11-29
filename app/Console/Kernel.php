<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array<int, class-string>
     */
    protected $commands = [
        \App\Console\Commands\CreateTenant::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Central app backup - daily at 2 AM
        $schedule->command('central:backup --db-only')
                 ->daily()
                 ->at('02:00')
                 ->name('central-daily-backup')
                 ->withoutOverlapping();

        // Weekly full central backup (with files) - Sundays at 3 AM
        $schedule->command('central:backup')
                 ->weekly()
                 ->sundays()
                 ->at('03:00')
                 ->name('central-weekly-backup')
                 ->withoutOverlapping();

        // All tenants backup - daily at 1 AM
        $schedule->command('tenant:backup --all-tenants')
                 ->daily()
                 ->at('01:00')
                 ->name('tenant-daily-backup')
                 ->withoutOverlapping();

        // Clean old backups - daily at 4 AM
        $schedule->command('backup:clean')
                 ->daily()
                 ->at('04:00')
                 ->name('backup-cleanup')
                 ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
