<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User permissions
            'manage users',
            'view users',
            'create users',
            'edit users',
            'delete users',
            'view own profile',
            'edit own profile',

            // Company permissions
            'manage companies',
            'view companies',
            'create companies',
            'edit companies',
            'delete companies',

            // Team permissions
            'manage teams',
            'view teams',
            'create teams',
            'edit teams',
            'delete teams',

            // Transaction permissions
            'manage transactions',
            'view transactions',
            'create transactions',
            'edit transactions',
            'delete transactions'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Assign admin permissions to specific user
        $adminUser = User::find(12);
        if ($adminUser) {
            $adminUser->givePermissionTo([
                // User permissions
                'manage users',
                'view users',
                'create users',
                'edit users',
                'delete users',
                'view own profile',
                'edit own profile',

                // Company permissions
                'manage companies',
                'view companies',
                'create companies',
                'edit companies',
                'delete companies',

                // Team permissions
                'manage teams',
                'view teams',
                'create teams',
                'edit teams',
                'delete teams',

                // Transaction permissions
                'manage transactions',
                'view transactions',
                'create transactions',
                'edit transactions',
                'delete transactions'
            ]);
        }

        // Assign basic permissions to all other users
        $users = User::where('id', '!=', 12)->get();
        foreach ($users as $user) {
            $user->givePermissionTo([
                'view own profile',
                'edit own profile'
            ]);
        }
    }
}
