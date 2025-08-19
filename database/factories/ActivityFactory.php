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
            'customer_id' => fn() => Customer::inRandomOrder()->first()?->id ?? 
                              Customer::factory()->create()->id,
                              
            'user_id' => fn() => User::inRandomOrder()->first()?->id ?? 
                         User::factory()->create()->id,
            
            'activity_follow_up_id' => fn() => ActivityFollowUp::inRandomOrder()->first()?->id ?? 
                                      ActivityFollowUp::factory()->create()->id,

            'activity_type' => $this->faker->randomElement(['Call', 'Email', 'Meeting', 'Follow-up', 'Sale', 'Support', 'Complaint']),
            // status and level_score should be random from activity_follow_up table
            'status' => fn() => ActivityFollowUp::inRandomOrder()->first()?->status ?? 'Pending',
            'level_score' => fn() => ActivityFollowUp::inRandomOrder()->first()?->level_score ?? 1,
            'notes' => $this->faker->paragraph(),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fn (array $attributes) => Carbon::parse($attributes['created_at'])->addDays(rand(1, 30)),
        ];
    }
}