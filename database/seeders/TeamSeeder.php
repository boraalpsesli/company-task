<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        // Create teams for the default company
        $defaultCompany = Company::where('name', 'Default Company')->first();
        
        Team::create([
            'name' => 'Admin Team',
            'company_id' => $defaultCompany->id,
        ]);

        Team::create([
            'name' => 'Development Team',
            'company_id' => $defaultCompany->id,
        ]);

        // Create 2-3 teams for each company
        Company::all()->each(function ($company) {
            Team::factory(rand(2, 3))->create([
                'company_id' => $company->id,
            ]);
        });
    }
} 