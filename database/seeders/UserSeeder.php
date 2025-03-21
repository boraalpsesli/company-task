<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Get the admin team
        $adminTeam = Team::where('name', 'Admin Team')->first();
        
        // Create a default admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'national_id' => '1234567890',
            'company_id' => $adminTeam->company_id,
            'team_id' => $adminTeam->id,
        ]);

        // Create 10 random users
        User::factory(10)->create();
    }
} 