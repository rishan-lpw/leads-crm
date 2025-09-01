<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ActivityFollowUp>
 */
class ActivityFollowUpFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
        return [
            'activity_id' => \App\Models\Activity::factory(),
            'follow_up_time' => $this->faker->optional()->dateTimeBetween('now', '+30 days'),
            'status' => $this->faker->randomElement(['pending', 'done', 'missed', 'overdue']),
            'level_score' => $this->faker->numberBetween(0, 100),
            'reminder_at' => $this->faker->optional()->dateTimeBetween('now', '+30 days'),
            'completed_at' => $this->faker->optional()->dateTimeBetween('-30 days', 'now'),
            'auto_status_updated' => $this->faker->boolean(20), // 20% chance of being true
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
