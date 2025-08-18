<?php

namespace Database\Factories;

use App\Models\Activity;
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
        $activityTypes = ['Call', 'Email', 'Meeting', 'Follow-up', 'Sale', 'Support', 'Complaint'];
        $statuses = ['Pending', 'Completed', 'Cancelled', 'In Progress', 'Scheduled'];
        
        return [
            'customer_id' => Customer::inRandomOrder()->first()?->id ?? 1,
            'user_id' => User::inRandomOrder()->first()?->id ?? 1,
            'activity_type' => $this->faker->randomElement($activityTypes),
            'status' => $this->faker->randomElement($statuses),
            'level_score' => $this->faker->numberBetween(1, 4),
            'notes' => $this->faker->paragraph(),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fn (array $attributes) => Carbon::parse($attributes['created_at'])->addDays(rand(1, 30)),
        ];
    }
}