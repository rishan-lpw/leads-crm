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

    // $table->timestamp('follow_up_time');
    // $table->text('status');
    // $table->unsignedBigInteger('level_score');

    public function definition(): array
    {
        return [
            'follow_up_time' => Carbon::now()->addDays(rand(1, 30)), 
            'status' => $this->faker->randomElement(['pending', 'completed', 'cancelled']),
            'level_score' => $this->faker->numberBetween(1, 5), 
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fn (array $attributes) => Carbon::parse($attributes['created_at'])->addDays(rand(1, 30)),
        ];
    }
}
