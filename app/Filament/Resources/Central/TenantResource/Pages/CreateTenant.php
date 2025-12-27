<?php

namespace App\Filament\Resources\Central\TenantResource\Pages;

use App\Filament\Resources\Central\TenantResource;
use Filament\Resources\Pages\CreateRecord;
use Exception;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    protected ?string $domainInput = null;

    protected bool $shouldSendSuccessNotification = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Clean any stray output
        if (ob_get_level()) {
            ob_clean();
        }

        $this->domainInput = $data['domain'] ?? null;
        unset($data['domain']);
        return $data;
    }

    protected function afterCreate(): void
    {
        if (!$this->record) {
            return;
        }

        // Create domain
        if ($this->domainInput) {
            try {
                $this->record->domains()->create([
                    'domain' => $this->domainInput,
                ]);
            } catch (Exception $e) {
                \Log::error('Domain creation error: ' . $e->getMessage());
            }
        }

        // Create default admin user and tenant owner user for the tenant
        try {
            $this->record->run(function () {
                // Get admin email from config
                $adminEmail = config('mail.admin_mail');
                $tenantEmail = $this->record->email;
                $domain = $this->domainInput ?? ($this->record->domains->first()->domain ?? null);
                $loginUrl = $domain ? "https://{$domain}/login" : route('login');

                // Seed roles and permissions for this tenant
                try {
                    \Artisan::call('db:seed', [
                        '--class' => 'Database\\Seeders\\RolesAndPermissionsSeeder',
                    ]);
                } catch (Exception $seedError) {
                    \Log::error('Failed to seed roles and permissions: ' . $seedError->getMessage());
                }

                // Create admin user (if admin email is configured)
                if ($adminEmail && $adminEmail !== $tenantEmail) {
                    $adminPassword = Str::random(12);

                    $adminUser = \App\Models\User::firstOrCreate([
                        'email' => $adminEmail,
                    ], [
                        'name' => 'Admin',
                        'email' => $adminEmail,
                        'password' => \Illuminate\Support\Facades\Hash::make($adminPassword),
                    ]);

                    // Assign all roles to admin user
                    try {
                        $roles = \Spatie\Permission\Models\Role::all();
                        $adminUser->syncRoles($roles);
                    } catch (Exception $roleError) {
                        \Log::error('Failed to assign roles to admin: ' . $roleError->getMessage());
                    }

                    // Send admin credentials email
                    try {
                        Mail::send('emails.tenant-credentials', [
                            'tenant_name' => $this->record->name,
                            'email' => $adminEmail,
                            'password' => $adminPassword,
                            'login_url' => $loginUrl,
                            'user_type' => 'Admin',
                        ], function ($message) use ($adminEmail) {
                            $message->to($adminEmail)
                                ->subject('Your Tenant Admin Account Credentials');
                        });
                    } catch (Exception $mailError) {
                        \Log::error('Failed to send admin credentials email: ' . $mailError->getMessage());
                    }
                }

                // Create tenant owner user
                $tenantPassword = Str::random(12);

                \App\Models\User::firstOrCreate([
                    'email' => $tenantEmail,
                ], [
                    'name' => $this->record->name . ' Owner',
                    'email' => $tenantEmail,
                    'password' => \Illuminate\Support\Facades\Hash::make($tenantPassword),
                ]);

                // Send tenant owner credentials email
                try {
                    Mail::send('emails.tenant-credentials', [
                        'tenant_name' => $this->record->name,
                        'email' => $tenantEmail,
                        'password' => $tenantPassword,
                        'login_url' => $loginUrl,
                        'user_type' => 'Owner',
                    ], function ($message) use ($tenantEmail) {
                        $message->to($tenantEmail)
                            ->subject('Your Tenant Account Credentials - ' . $this->record->name);
                    });
                } catch (Exception $mailError) {
                    \Log::error('Failed to send tenant credentials email: ' . $mailError->getMessage());
                }
            });
        } catch (Exception $e) {
            \Log::error('Tenant initialization error: ' . $e->getMessage());
        }

        // Clean any output from tenant creation
        if (ob_get_level()) {
            ob_clean();
        }
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return null;
    }

    protected function getRedirectUrl(): string
    {
        // Use the resource's index URL
        return $this->getResource()::getUrl('index');
    }
}
