<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        $roles = [
            'Admin',
            'Manager',
            'Cashier',
            'Server',
            'Waiter',
            'Kitchen Staff',
            'Inventory Manager',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // You can also create permissions here if needed
        // Example:
        // Permission::create(['name' => 'view orders']);
        // Permission::create(['name' => 'create orders']);

        $this->command->info('Roles and permissions seeded successfully!');
    }
}
