<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ListCentralUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'central:list-users {--format=table : Output format (table, json)}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'List all users from the central app database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $format = $this->option('format');

        $users = User::all(['id', 'name', 'email', 'created_at', 'updated_at']);

        if ($users->isEmpty()) {
            $this->info('No users found in the central database.');
            return self::SUCCESS;
        }

        if ($format === 'json') {
            $this->line(json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $this->table(
                ['ID', 'Name', 'Email', 'Created At', 'Updated At'],
                $users->map(function ($user) {
                    return [
                        $user->id,
                        $user->name,
                        $user->email,
                        $user->created_at,
                        $user->updated_at,
                    ];
                })->toArray()
            );

            $this->info("Total users: {$users->count()}");
        }

        return self::SUCCESS;
    }
}
