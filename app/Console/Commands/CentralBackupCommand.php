<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class CentralBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'central:backup {--files} {--db-only} {--with-notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a backup of the central application (database, files, or both)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating central application backup...');

        // Make sure we're not in tenant context
        if (app()->bound('tenant') && tenant()) {
            $this->error('Cannot run central backup from tenant context.');
            return Command::FAILURE;
        }

        try {
            $options = [];

            if ($this->option('db-only')) {
                $options['--only-db'] = true;
                $this->info('Backing up database only...');
            } elseif ($this->option('files')) {
                $options['--only-files'] = true;
                $this->info('Backing up files only...');
            } else {
                $this->info('Backing up database and files...');
            }

            if (!$this->option('with-notifications')) {
                $options['--disable-notifications'] = true;
            }

            Artisan::call('backup:run', $options);

            $output = Artisan::output();
            $this->line($output);

            $this->info('✅ Central backup completed successfully!');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Central backup failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
