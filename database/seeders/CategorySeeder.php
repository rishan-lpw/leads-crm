<?php

namespace Database\Seeders;
use \App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        foreach (['Electronics', 'Home & Garden', 'Automotive', 'Fashion'] as $name) {
            Category::firstOrCreate(
                ['name' => $name],
                ['status' => 'active', 'description' => $name . ' description']
            );
        }

        // Category::factory()
        //     ->count(4)
        //     ->create();
    }
}
