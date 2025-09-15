<?php

namespace Database\Factories;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            'lead_id' => 1, // Use a fixed ID for now to avoid relationship issues
            'activity_type' => $this->faker->randomElement(['call', 'email', 'meeting', 'whatsapp', 'sms', 'payment', 'site_visit', 'other']),
            'notes' => $this->faker->optional()->sentence(),
            'scheduled_at' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
            'due_at' => $this->faker->optional()->dateTimeBetween('+1 day', '+2 months'),
            'last_checked_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'activity_follow_up_id' => null, // Keep null for now
            'action' => $this->faker->optional()->word(),
            'qty' => $this->faker->optional()->numberBetween(1, 10),
            'value' => $this->faker->optional()->word(),
            'ad_id' => $this->faker->optional()->numberBetween(1, 50),
            'comments' => $this->faker->optional()->sentence(),
            'date_time' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'assigned_by' => null, // Keep null for now
            'old_am' => null, // Keep null for now
        ];
    }
}