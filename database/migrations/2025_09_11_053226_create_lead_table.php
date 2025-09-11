<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lead', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('ad_id')->unsigned();
            $table->bigInteger('cust_id')->unsigned();
            $table->bigInteger('user_id')->unsigned()->nullable();

            // Core fields
            $table->string('type', 50)->nullable();
            $table->string('propty_type', 100)->nullable();
            $table->string('service_type', 100)->nullable();
            $table->string('street', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('heading', 255)->nullable();
            $table->text('desc')->nullable();

            // Pricing
            $table->bigInteger('price')->nullable();
            $table->bigInteger('alt_price')->nullable();
            $table->string('alt_currency', 5)->nullable();
            $table->string('price_type', 50)->nullable();
            $table->bigInteger('price_monthly')->nullable();
            $table->bigInteger('price_land_pp')->nullable();
            $table->bigInteger('price_land_total')->nullable();

            // Media
            $table->boolean('pic')->default(false);
            $table->integer('pic_count')->nullable();
            $table->string('youtube_link', 255)->nullable();
            $table->string('video_link', 255)->nullable();
            $table->string('image_360', 255)->nullable();

            // Contact
            $table->string('contact_type', 50)->nullable();
            $table->string('contact_name', 255)->nullable();
            $table->string('email', 255)->nullable();

            // Location
            $table->decimal('lat', 12, 8)->nullable();
            $table->decimal('lng', 12, 8)->nullable();

            // Status and Source
            $table->string('status', 50)->nullable()->default('new');
            $table->string('source', 100)->nullable();

            // Flags (use tinyint instead of varchar!)
            $table->tinyInteger('blocked')->default(0);
            $table->tinyInteger('is_active')->default(0);
            $table->tinyInteger('is_trending')->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead');
    }
};
