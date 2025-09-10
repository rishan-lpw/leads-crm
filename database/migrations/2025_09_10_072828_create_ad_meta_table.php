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
        Schema::create('ad_meta', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ad_id');
            $table->string('contact_type', 50)->nullable();
            $table->string('contact_name', 100)->nullable();
            $table->string('tel', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->decimal('lat', 20, 15)->nullable();
            $table->decimal('lng', 20, 15)->nullable();
            $table->integer('zoom')->nullable();
            $table->string('last_ip', 50)->nullable();
            $table->string('poster_ip', 50)->nullable();
            $table->integer('hits')->default(0);
            $table->boolean('was_active')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_blocked')->default(false);
            $table->string('block_reason', 255)->nullable();
            $table->boolean('is_trending')->default(false);
            $table->timestamp('trending_date')->nullable();
            $table->integer('boosted_count')->default(0);
            $table->boolean('hot_deals')->default(false);
            $table->string('house_post_url', 255)->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_meta');
    }
};
