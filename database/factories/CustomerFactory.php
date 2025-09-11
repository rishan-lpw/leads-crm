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

        // Generate random membership expiry date (some expired, some active)
        $membershipExpDate = $this->faker->dateTimeBetween('-6 months', '+1 year');
        $paymentExpDate = $this->faker->dateTimeBetween('-3 months', '+6 months');
        
        // Determine membership status based on expiry dates
        $membershipStatus = 'active';
        if ($membershipExpDate < now()) {
            $membershipStatus = $this->faker->randomElement(['expired', 'suspended']);
        } else {
            $membershipStatus = $this->faker->randomElement(['active', 'inactive']);
        }
        
        return [
            'firstname' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'mobile' => $this->faker->phoneNumber(),
            'mobile_alt' => $this->faker->optional()->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'address' => $this->faker->optional()->address(),
            'add_id' => null, // Assuming no add-ons for simplicity
            'role_id' => $roleId,
            'membership_exp_date' => $membershipExpDate,
            'payment_exp_date' => $paymentExpDate,
            'membership_status' => $membershipStatus,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

}