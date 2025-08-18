<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Get random user_id from users table
        $userId = User::inRandomOrder()->first()?->id ?? 1;
        
        // Use random role_id 1-5 (assuming you have at least 5 roles)
        $roleId = rand(1, 5);
        
        return [
            'name' => $this->faker->name,
            'phone_number' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'password' => bcrypt('password'), // Use bcrypt for password hashing
            'address' => $this->faker->address,
            'add_id' => $userId,
            'role_id' => $roleId,
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fn (array $attributes) => Carbon::parse($attributes['created_at'])->addDays(rand(1, 10)),
        ];
    }
}