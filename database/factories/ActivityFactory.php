<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\Lead;
use App\Models\User;
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
        // 'lead_id',
        // 'activity_type',
        // 'notes',
        // 'scheduled_at',
        // 'due_at',
        // 'last_checked_at',
        // 'action',
        // 'qty',
        // 'value',
        // 'ad_id',
        // 'comments',
        // 'date_time',
        // 'assigned_by',
        // 'old_am',
        // 'activity_follow_up_id',
        return [
            'lead_id' => Lead::factory(), // Create a lead or use existing one
            'activity_type' => $this->faker->randomElement(['call', 'email', 'meeting', 'whatsapp', 'sms', 'payment', 'site_visit', 'other']),
            'notes' => $this->faker->optional()->sentence(),
            'scheduled_at' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
            'due_at' => $this->faker->optional()->dateTimeBetween('+1 day', '+2 months'),
            'last_checked_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'activity_follow_up_id' => null,
            'action' => $this->faker->optional()->randomElement(['view', 'call', 'email', 'visit', 'follow_up']),
            'qty' => $this->faker->optional()->numberBetween(1, 10),
            'value' => $this->faker->optional()->randomFloat(2, 0, 9999.99), // Changed from word() to decimal
            'ad_id' => $this->faker->optional()->numberBetween(1000, 9999),
            'comments' => $this->faker->optional()->sentence(),
            'date_time' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'assigned_by' => User::factory(), // Create a user or use existing one
            'old_am' => $this->faker->optional()->name(),
        ];
    }

    /**
     * Create activity with existing lead
     */
    public function forLead($leadId): static
    {
        return $this->state(fn (array $attributes) => [
            'lead_id' => $leadId,
        ]);
    }

    /**
     * Create activity with existing user
     */
    public function byUser($userId): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_by' => $userId,
        ]);
    }

    /**
     * Create completed activity
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'last_checked_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Create high priority activity
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
        ]);
    }
}