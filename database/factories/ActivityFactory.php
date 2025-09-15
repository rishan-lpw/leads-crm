<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\ActivityFollowUp;
use App\Models\Customer;
use App\Models\Lead;
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
            'lead_id' => Lead::inRandomOrder()->first()?->id ?? Lead::factory()->create()->id,
            'activity_type' => $this->faker->randomElement(['call', 'email', 'meeting', 'whatsapp', 'sms', 'payment', 'site_visit', 'other']),
            'notes' => $this->faker->sentence(),
            'scheduled_at' => Carbon::now()->addDays($this->faker->numberBetween(1, 30)),
            'due_at' => Carbon::now()->addDays($this->faker->numberBetween(1, 30)),
            'last_checked_at' => Carbon::now()->subDays($this->faker->numberBetween(1, 30)),
            'activity_follow_up_id' => ActivityFollowUp::inRandomOrder()->first()?->id ?? null,
            'action' => $this->faker->randomElement(['pending', 'completed', 'canceled']),
            'qty' => $this->faker->numberBetween(1, 100),
            'value' => $this->faker->randomFloat(2, 10, 1000),
            'ad_id' => $this->faker->uuid(),
            'comments' => $this->faker->paragraph(),
            'date_time' => Carbon::now()->subDays($this->faker->numberBetween(1, 30))->addHours($this->faker->numberBetween(0, 23))->addMinutes($this->faker->numberBetween(0, 59)),
            'assigned_by' => User::inRandomOrder()->first()?->id ?? null,
            'old_am' => Customer::inRandomOrder()->first()?->id ?? null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}