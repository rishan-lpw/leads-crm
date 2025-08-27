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
            'user_type_id' => $this->faker->randomElement([1, 2]),
            'name' => $this->faker->name(),
            'posted_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'source' => $this->faker->randomElement(['Ikman', 'Facebook-Ads', 'Website', 'Direct']),
            'am' => $this->faker->randomElement(['John Doe', 'Jane Smith', 'Mike Johnson', 'Sarah Wilson']),
            'status' => $this->faker->randomElement(['new', 'follow_up', 'system', 'closed']),
            'latest_comments' => $this->faker->sentence(10),
            // 'last_update_date' => $this->faker->dateTimeBetween('-7 days', 'now'),
            'last_update_by' => $this->faker->randomElement(['Admin', 'Manager', 'Agent', 'System']),
            'tel' => $this->faker->phoneNumber(),
            'price' => $this->faker->randomFloat(2, 10000, 5000000), // Price between 10k to 5M
        ];
    }
}
