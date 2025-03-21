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
        
        // Create a default admin user if it doesn't exist
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'national_id' => '1234567890',
                'company_id' => $adminTeam->company_id,
                'team_id' => $adminTeam->id,
            ]
        );

        // Create 50 random users
        User::factory(50)->create();
    }
} 