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

        // Create permissions
        $permissions = [
            // Page Builder permissions
            'view pages',
            'create pages',
            'edit pages',
            'delete pages',
            'publish pages',
            'override open price',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        // Assign permissions to roles
        $adminRole   = Role::where('name', 'Admin')->first();
        $managerRole = Role::where('name', 'Manager')->first();

        if ($adminRole) {
            $adminRole->syncPermissions($permissions);
        }

        if ($managerRole) {
            $managerRole->givePermissionTo('override open price');
        }

        $this->command->info('Roles and permissions seeded successfully!');
    }
}
