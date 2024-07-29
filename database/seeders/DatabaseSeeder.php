<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Voter;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin::factory(1)->create();
        // Voter::factory()->count(8)->create(['is_candidate' => 1]);
        Voter::factory()->count(15000)->create(['is_candidate' => 0]);
    }
}
