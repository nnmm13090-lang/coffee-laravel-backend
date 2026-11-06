<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'orders.view-all',
            'orders.update-status',
            'products.manage',
            'categories.manage',
            'banners.manage',
            'coupons.manage',
            'users.manage',
        ];

        foreach ($permissions as $name) {
            Permission::findOrCreate($name);
        }

        Role::findOrCreate('customer');

        Role::findOrCreate('staff')->syncPermissions([
            'orders.view-all',
            'orders.update-status',
        ]);

        Role::findOrCreate('admin')->syncPermissions($permissions);

        // Demo accounts (change the passwords before going live!)
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin', 'password' => 'password']
        )->syncRoles('admin');

        User::firstOrCreate(
            ['email' => 'staff@example.com'],
            ['name' => 'Barista Staff', 'password' => 'password']
        )->syncRoles('staff');
    }
}
