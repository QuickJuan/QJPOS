<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class UpdateCentralUserPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'central:update-password {user : User ID or email} {--password= : Password (will prompt if not provided)}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Update password for a central app user (only allowed if APP_DEBUG is true)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Check if debug mode is enabled
        if (!config('app.debug')) {
            $this->error('This command is only allowed when APP_DEBUG is set to true.');
            return self::FAILURE;
        }

        $userIdentifier = $this->argument('user');

        // Find user by ID or email
        $user = User::where('id', $userIdentifier)
            ->orWhere('email', $userIdentifier)
            ->first();

        if (!$user) {
            $this->error("User not found with identifier: {$userIdentifier}");
            return self::FAILURE;
        }

        // Get password from option or prompt
        $password = $this->option('password');
        if (!$password) {
            $password = $this->secret('Enter new password');
            $confirmPassword = $this->secret('Confirm password');

            if ($password !== $confirmPassword) {
                $this->error('Passwords do not match.');
                return self::FAILURE;
            }
        }

        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters long.');
            return self::FAILURE;
        }

        // Update password
        $user->update([
            'password' => Hash::make($password),
        ]);

        $this->info("Password updated successfully for user: {$user->email}");
        return self::SUCCESS;
    }
}
