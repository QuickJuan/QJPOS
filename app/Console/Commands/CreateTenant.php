<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;
use App\Models\Central\Tenant;
use Stancl\Tenancy\Database\Models\Domain;

class CreateTenant extends Command
{
    protected $signature = 'tenant:create
        {domain : The tenant primary domain (e.g. acme.quickjuan.site)}
        {--id= : Explicit tenant ID}
        {--name= : Display name for the tenant}
        {--email= : Contact email}
        {--phone= : Contact phone}
        {--address= : Address line}
        {--city= : City}
        {--state= : State / Province}
        {--country= : Country}
        {--subscription= : Subscription plan code}
        {--billing_type= : Billing type (e.g. monthly, yearly)}
        {--subscription_status= : Subscription status (active, trialing, cancelled)}
        {--subscription_ends_at= : Subscription end date (YYYY-MM-DD)}
        {--db-name= : Explicit database name override}
        {--non-interactive : Skip interactive prompts and use only provided flags}
        {--skip-migrate : Skip tenant migrations}
    ';

    protected $description = 'Create a new tenant with domain and optional metadata';

    public function handle(): int
    {
        $domain = trim($this->argument('domain'));
        if (Domain::where('domain', $domain)->exists()) {
            $this->error("Domain '{$domain}' already exists.");
            return 1;
        }

        $providedId = $this->option('id');
        $tenantId = $providedId ?: Str::slug(explode('.', $domain)[0]);
        if ($tenantId === '') {
            $tenantId = Str::lower(Str::random(12));
        }

        if (Tenant::where('id', $tenantId)->exists()) {
            $this->error("Tenant ID '{$tenantId}' already exists. Choose another --id.");
            return 1;
        }

        // Interactive prompts unless --non-interactive set
        $interactive = !$this->option('non-interactive');

        $name = $this->option('name');
        $email = $this->option('email');
        $phone = $this->option('phone');
        $address = $this->option('address');
        $city = $this->option('city');
        $state = $this->option('state');
        $country = $this->option('country');
        $subscription = $this->option('subscription');
        $billingType = $this->option('billing_type');
        $subscriptionStatus = $this->option('subscription_status');
        $subscriptionEndsAt = $this->option('subscription_ends_at');
        $explicitDbName = $this->option('db-name');

        if ($interactive) {
            $this->line('--- Tenant Metadata (press enter to keep current/default) ---');
            $name = $name ?: $this->ask('Display Name', Str::title($tenantId));
            $email = $email ?: $this->ask('Email (optional)');
            if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->error('Invalid email format.');
                return 1;
            }
            $phone = $phone ?: $this->ask('Phone (optional)');
            $address = $address ?: $this->ask('Address (optional)');
            $city = $city ?: $this->ask('City (optional)');
            $state = $state ?: $this->ask('State (optional)');
            $country = $country ?: $this->ask('Country (optional)');
            $subscription = $subscription ?: $this->ask('Subscription plan code (optional)');
            $billingType = $billingType ?: $this->ask('Billing type (monthly/yearly/etc)');
            $subscriptionStatus = $subscriptionStatus ?: $this->ask('Subscription status (optional)', 'active');
            $subscriptionEndsAt = $subscriptionEndsAt ?: $this->ask('Subscription ends at (YYYY-MM-DD optional)');
            $explicitDbName = $explicitDbName ?: $this->ask('Explicit database name (optional, overrides prefix+id+suffix)');
        }

        // Build database name using config prefix/suffix unless explicit given
        $prefix = config('tenancy.database.prefix', 'tenant');
        $suffix = config('tenancy.database.suffix', '');
        $dbName = $explicitDbName ?: ($prefix . $tenantId . $suffix);

        if ($explicitDbName && Tenant::where('tenancy_db_name', $explicitDbName)->exists()) {
            $this->error("Explicit database name '{$explicitDbName}' already in use by another tenant.");
            return 1;
        }

        // Confirmation prompt
        if ($interactive) {
            $this->table(['Field', 'Value'], [
                ['ID', $tenantId],
                ['Domain', $domain],
                ['DB Name', $dbName],
                ['Name', $name],
                ['Email', $email],
                ['Phone', $phone],
                ['Address', $address],
                ['City', $city],
                ['State', $state],
                ['Country', $country],
                ['Subscription', $subscription],
                ['Billing Type', $billingType],
                ['Subscription Status', $subscriptionStatus],
                ['Subscription Ends At', $subscriptionEndsAt],
            ]);
            if (!$this->confirm('Proceed with creation?', true)) {
                $this->info('Aborted.');
                return 0;
            }
        }

        $tenant = Tenant::create([
            'id' => $tenantId,
            'tenancy_db_name' => $dbName,
            'name' => $name ?: Str::title($tenantId),
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'country' => $country,
            'subscription' => $subscription,
            'billing_type' => $billingType,
            'subscription_status' => $subscriptionStatus,
            'subscription_ends_at' => $subscriptionEndsAt ?: null,
        ]);

        $tenant->domains()->create(['domain' => $domain]);

        $this->info("Tenant created: ID={$tenant->id} DB={$dbName} Domain={$domain}");
        if ($interactive) {
            $this->table(['Field','Value'], [
                ['ID', $tenant->id],
                ['Domain', $domain],
                ['DB', $dbName],
                ['Name', $tenant->name],
                ['Email', $tenant->email],
                ['Subscription', $tenant->subscription],
                ['Status', $tenant->subscription_status],
                ['Ends At', $tenant->subscription_ends_at],
            ]);
        }

        if (!$this->option('skip-migrate')) {
            $this->info('Running tenant migrations...');
            Artisan::call('tenants:migrate', ['--tenants' => [$tenant->id]]);
            $this->line(Artisan::output());
        } else {
            $this->comment("Skipped migrations. Run later: php artisan tenants:migrate --tenants={$tenant->id}");
        }

        $this->info('Done.');
        return 0;
    }
}
