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

        // Roles
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
            Role::firstOrCreate([
                'name'       => $roleName,
                'guard_name' => 'web',
            ]);
        }

        // Permissions (tenant app + Filament admin)
        $permissions = [
            // Page Builder / CMS
            'view pages',
            'create pages',
            'edit pages',
            'delete pages',
            'publish pages',

            // Products & menus
            'view products',
            'create products',
            'edit products',
            'delete products',
            'manage modifiers',
            'manage categories',
            'manage discounts',
            'override open price',

            // Tables & orders
            'view tables',
            'manage tables',
            'manage table rooms',
            'manage orders',
            'manage receipts',
            'void item',
            'cancel transaction',
            'give discount',
            'print bill',

            // Cashier / finance
            'open cashier session',
            'close cashier session',
            'process payments',
            'refund payments',

            // Staff & roles
            'view users',
            'create users',
            'edit users',
            'delete users',
            'assign roles',
            'assign permissions',

            // Attendance & shifts
            'record attendance',
            'view attendance',

            // Inventory
            'view inventory',
            'adjust inventory',

            // Settings & system
            'view settings',
            'update settings',
            'manage backups',
            'view reports',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name'       => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        // Map permissions to roles
        $rolePermissions = [
            'Admin' => $permissions,
            'Manager' => [
                'view pages','create pages','edit pages','delete pages','publish pages',
                'view products','create products','edit products','delete products','manage modifiers','manage categories','manage discounts','override open price',
                'view tables','manage tables','manage table rooms','manage orders','manage receipts','void item','cancel transaction','give discount','print bill',
                'open cashier session','close cashier session','process payments','refund payments',
                'view users','create users','edit users','assign roles','assign permissions',
                'record attendance','view attendance',
                'view inventory','adjust inventory',
                'view settings','update settings','manage backups','view reports',
            ],
            'Cashier' => [
                'view products','manage discounts','override open price',
                'view tables','manage orders','manage receipts','void item','cancel transaction','give discount','print bill',
                'open cashier session','close cashier session','process payments','refund payments',
                'record attendance','view attendance',
                'view reports',
            ],
            'Server' => [
                'view products','manage orders','manage receipts','print bill',
                'view tables',
                'record attendance','view attendance',
            ],
            'Waiter' => [
                'view products','manage orders','view tables','manage receipts','print bill',
                'record attendance','view attendance',
            ],
            'Kitchen Staff' => [
                'view products','manage orders','view tables',
            ],
            'Inventory Manager' => [
                'view products','create products','edit products','delete products','manage modifiers','manage categories','manage discounts',
                'view inventory','adjust inventory',
                'view reports',
            ],
        ];

        foreach ($rolePermissions as $roleName => $perms) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->syncPermissions($perms);
            }
        }

        $this->command?->info('Roles and permissions seeded successfully!');
    }
}
