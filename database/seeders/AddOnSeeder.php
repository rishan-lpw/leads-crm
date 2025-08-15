<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AddOn;

class AddOnSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {
        AddOn::factory()
            ->count(10)
            ->create();
    }
}
