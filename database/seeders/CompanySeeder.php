<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        // Create a default company
        Company::create([
            'name' => 'Default Company',
            'balance' => 1000000.00,
        ]);

        // Create 5 random companies
        Company::factory(5)->create();
    }
} 