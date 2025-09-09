<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\ActivityFollowUp;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
        return [
            'lead_id' => $this->faker->numberBetween(1, 50),
            'activity_type' => $this->faker->randomElement(['call', 'email', 'meeting', 'follow_up', 'message']),
            'notes' => $this->faker->optional()->sentence(10),
            'scheduled_at' => $this->faker->optional()->dateTimeBetween('now', '+30 days'),
            'due_at' => $this->faker->optional()->dateTimeBetween('now', '+30 days'),
            'last_checked_at' => $this->faker->optional()->dateTimeBetween('-30 days', 'now'),
            'auto_status_updated' => $this->faker->boolean(20), // 20% chance of being true
        ];
    }
}