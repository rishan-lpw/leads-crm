<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a test user first (needed for role references)
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'user_level_id' => 1,
            'password' => bcrypt('password'),
        ]);
        
        // Get the first user's ID
        $userId = User::first()->id;
        
        // Create default roles if none exist
        if (DB::table('role')->count() === 0) {
            DB::table('role')->insert([
                [
                    'role_name' => 'Admin', 
                    'description' => 'Administrative role', 
                    'assign_date' => now(),
                    'assigned_by' => $userId,
                    'created_at' => now(), 
                    'updated_at' => now()
                ],
                [
                    'role_name' => 'Customer', 
                    'description' => 'Regular customer', 
                    'assign_date' => now(),
                    'assigned_by' => $userId,
                    'created_at' => now(), 
                    'updated_at' => now()
                ],
                [
                    'role_name' => 'Lead', 
                    'description' => 'Potential customer', 
                    'assign_date' => now(),
                    'assigned_by' => $userId,
                    'created_at' => now(), 
                    'updated_at' => now()
                ],
                [
                    'role_name' => 'VIP', 
                    'description' => 'Very important customer', 
                    'assign_date' => now(),
                    'assigned_by' => $userId,
                    'created_at' => now(), 
                    'updated_at' => now()
                ],
                [
                    'role_name' => 'Partner', 
                    'description' => 'Business partner', 
                    'assign_date' => now(),
                    'assigned_by' => $userId,
                    'created_at' => now(), 
                    'updated_at' => now()
                ],
            ]);
        }

        // Run other seeders
        $this->call([
            CustomerSeeder::class,
            ActivitySeeder::class,
        ]);
    }
}
