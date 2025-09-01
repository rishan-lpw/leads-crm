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
            'name' => $this->faker->name,
            'phone_number' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'password' => bcrypt('password'), // Use bcrypt for password hashing
            'address' => $this->faker->address,
            'add_id' => $userId,
            'role_id' => $roleId,

            // New membership fields
            'membership_status' => $membershipStatus,
            'membership_category' => $this->faker->randomElement(['basic', 'premium', 'vip']),
            'payment_exp_date' => $paymentExpDate,
            'membership_exp_date' => $membershipExpDate,
            'available_boosts_source' => $this->faker->numberBetween(0, 50),
            'last_boost_added_date' => $this->faker->optional(0.7)->dateTimeBetween('-3 months', 'now'),
            'ad_url' => $this->faker->optional(0.6)->url(),
            'customer_remarks' => $this->faker->optional(0.8)->realText(200),

            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fn (array $attributes) => Carbon::parse($attributes['created_at'])->addDays(rand(1, 10)),
        ];
    }

    public function activeMembership(): static
    {
        return $this->state(fn (array $attributes) => [
            'membership_status' => 'active',
            'membership_category' => $this->faker->randomElement(['premium', 'vip']),
            'membership_exp_date' => $this->faker->dateTimeBetween('now', '+1 year'),
            'payment_exp_date' => $this->faker->dateTimeBetween('now', '+6 months'),
            'available_boosts_source' => $this->faker->numberBetween(10, 50),
            'last_boost_added_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'membership_status' => 'active',
            'membership_category' => 'premium',
            'membership_exp_date' => $this->faker->dateTimeBetween('now', '+1 year'),
            'payment_exp_date' => $this->faker->dateTimeBetween('now', '+6 months'),
            'available_boosts_source' => $this->faker->numberBetween(20, 50),
            'last_boost_added_date' => $this->faker->dateTimeBetween('-2 weeks', 'now'),
            'ad_url' => $this->faker->url(),
            'customer_remarks' => 'Premium customer with extended benefits.',
        ]);
    }

    public function vip(): static
    {
        return $this->state(fn (array $attributes) => [
            'membership_status' => 'active',
            'membership_category' => 'vip',
            'membership_exp_date' => $this->faker->dateTimeBetween('+6 months', '+2 years'),
            'payment_exp_date' => $this->faker->dateTimeBetween('+3 months', '+1 year'),
            'available_boosts_source' => $this->faker->numberBetween(30, 100),
            'last_boost_added_date' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'ad_url' => $this->faker->url(),
            'customer_remarks' => 'VIP customer with premium support and unlimited access.',
        ]);
    }
}