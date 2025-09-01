<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 10 regular customers with mixed memberships
        Customer::factory(10)->create();
        
        // Create 10 customers with active memberships
        Customer::factory(10)->activeMembership()->create();
        
        // Create 8 customers with expired memberships
        Customer::factory(8)->expiredMembership()->create();
        
        // Create 5 premium customers
        Customer::factory(5)->premium()->create();
        
        // Create 3 VIP customers
        Customer::factory(3)->vip()->create();
    }
}
