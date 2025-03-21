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
        $admin = Role::create(['name' => 'admin']);
        $permission_manage_user = Permission::create(['name' => 'manage users']);
       
        
        $admin->givePermissionTo($permission_manage_user);
        
        $user=User::find(13);
        $user->assignRole('admin');
    }
}
