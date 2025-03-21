<?php

namespace App\Console\Commands;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Illuminate\Console\Command;

class AssignAllPermissions extends Command
{
    protected $signature = 'user:give-all-permissions {user_id}';
    protected $description = 'Assign all permissions to a specific user';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $user = User::find($userId);

        if (!$user) {
            $this->error("User with ID {$userId} not found!");
            return 1;
        }

        $user->givePermissionTo(Permission::all());
        $this->info("All permissions successfully assigned to user: {$user->name} (ID: {$userId})");
        
        // Display assigned permissions
        $this->info("\nAssigned permissions:");
        foreach ($user->getAllPermissions() as $permission) {
            $this->line("- {$permission->name}");
        }
    }
} 