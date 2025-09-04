<?php

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => $this->faker->numberBetween(1, 10),
            'customer_id' => $this->faker->numberBetween(81, 100),
            'posted_date' => $this->faker->date(),
            'source' => $this->faker->randomElement(['pending payment', 'facebook ads', '']),
            'am' => $this->faker->name(),
            'status' => $this->faker->randomElement(['new', 'follow_up', 'system', 'to_be_expired', 'expired']),
            'latest_comments' => $this->faker->sentence(),
            'last_update_date' => $this->faker->date(),
            'last_update_by' => $this->faker->name(),
            'tel' => $this->faker->phoneNumber(),
            'price' => $this->faker->randomFloat(2, 1000, 10000),
        ];
    }
}
