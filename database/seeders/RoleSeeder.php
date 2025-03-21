<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'manage users',
            'view users',
            'create users',
            'edit users',
            'delete users',
            'view own profile',
            'edit own profile'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);

        // Assign permissions to admin role
        $adminRole->givePermissionTo(Permission::all());

        // Assign permissions to user role
        $userRole->givePermissionTo([
            'view own profile',
            'edit own profile'
        ]);

        // Assign admin role to specific user if exists
        $adminUser = User::find(13);
        if ($adminUser) {
            $adminUser->assignRole('admin');
        }

        // Assign user role to all other users
        $users = User::where('id', '!=', 13)->get();
        foreach ($users as $user) {
            $user->assignRole('user');
        }
    }
}
