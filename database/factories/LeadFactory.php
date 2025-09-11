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
        //     $table->id();
        //     $table->bigInteger('ad_id')->unsigned();
        //     $table->bigInteger('cust_id')->unsigned();
        //     $table->bigInteger('user_id')->unsigned()->nullable();

        //     // Core fields
        //     $table->string('type', 50)->nullable();
        //     $table->string('propty_type', 100)->nullable();
        //     $table->string('service_type', 100)->nullable();
        //     $table->string('street', 255)->nullable();
        //     $table->string('city', 100)->nullable();
        //     $table->string('heading', 255)->nullable();
        //     $table->text('desc')->nullable();

        //     // Pricing
        //     $table->bigInteger('price')->nullable();
        //     $table->bigInteger('alt_price')->nullable();
        //     $table->string('alt_currency', 5)->nullable();
        //     $table->string('price_type', 50)->nullable();
        //     $table->bigInteger('price_monthly')->nullable();
        //     $table->bigInteger('price_land_pp')->nullable();
        //     $table->bigInteger('price_land_total')->nullable();

        //     // Media
        //     $table->boolean('pic')->default(false);
        //     $table->integer('pic_count')->nullable();
        //     $table->string('youtube_link', 255)->nullable();
        //     $table->string('video_link', 255)->nullable();
        //     $table->string('image_360', 255)->nullable();

        //     // Contact
        //     $table->string('contact_type', 50)->nullable();
        //     $table->string('contact_name', 255)->nullable();
        //     $table->string('email', 255)->nullable();

        //     // Location
        //     $table->decimal('lat', 12, 8)->nullable();
        //     $table->decimal('lng', 12, 8)->nullable();

        //     // Flags (use tinyint instead of varchar!)
        //     $table->tinyInteger('blocked')->default(0);
        //     $table->tinyInteger('is_active')->default(0);
        //     $table->tinyInteger('is_trending')->default(0);
            
        //     $table->timestamps();
        return [
            'ad_id' => $this->faker->numberBetween(1, 500),
            'cust_id' => $this->faker->numberBetween(1, 100),
            'user_id' => $this->faker->optional()->numberBetween(1, 50),
            'type' => $this->faker->randomElement(['sale', 'rent']),
            'propty_type' => $this->faker->randomElement(['apartment', 'house', 'land']),
            'service_type' => $this->faker->randomElement(['buy', 'sell', 'rent']),
            'street' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'heading' => $this->faker->sentence(),
            'desc' => $this->faker->paragraph(),
            'price' => $this->faker->numberBetween(50000, 500000),
            'alt_price' => $this->faker->optional()->numberBetween(1000, 10000),
            'alt_currency' => $this->faker->optional()->currencyCode(),
            'price_type' => $this->faker->optional()->randomElement(['fixed', 'negotiable']),
            'price_monthly' => $this->faker->optional()->numberBetween(500, 5000),
            'price_land_pp' => $this->faker->optional()->numberBetween(1000, 10000),
            'price_land_total' => $this->faker->optional()->numberBetween(20000, 200000),
            'pic' => $this->faker->boolean(70), // 70% chance of having pictures
            'pic_count' => $this->faker->optional()->numberBetween(1, 20),
            'youtube_link' => $this->faker->optional()->url(),
            'video_link' => $this->faker->optional()->url(),
            'image_360' => $this->faker->optional()->url(),
            'contact_type' => $this->faker->randomElement(['owner', 'agent']),
            'contact_name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'lat' => $this->faker->optional()->latitude(),
            'lng' => $this->faker->optional()->longitude(),
            'blocked' => $this->faker->boolean(10), // 10% chance of being blocked
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
            'is_trending' => $this->faker->boolean(30), // 30% chance of being trending 
            'status' => $this->faker->randomElement(['new', 'follow_up', 'system', 'to_be_expired', 'expired']),
            'source' => $this->faker->randomElement(['pending_payments', 'ikman', 'facebook', 'other']),
        ];
    }
}
