<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminMail = config('mail.admin_mail') ?? env('ADMIN_MAIL');
        $password  = Str::random(10);

        $user = User::firstOrCreate([
            'email' => $adminMail,
        ], [
            'name'     => 'Admin',
            'email'    => $adminMail,
            'password' => Hash::make($password),
        ]);

        // Assign role to the user
        // Comment this out for a while since we aren't implemented the roles and permissions yet.
        // if ($user) {
        //     $user->assignRole('Admin');
        // }

        // Send credentials to admin email if admin email is provided
        if ($adminMail) {
            Mail::raw(
                "Your admin account has been created.\nEmail: $adminMail\nPassword: $password",
                function ($message) use ($adminMail) {
                    $message->to($adminMail)
                        ->subject('Account Credentials');
                }
            );
        }
    }
}
