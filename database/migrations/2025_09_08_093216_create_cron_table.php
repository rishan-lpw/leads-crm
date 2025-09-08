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
        Schema::create('cron', function (Blueprint $table) {
            $table->id();
            // User-given fields
            $table->string('name');                       // Cron name
            $table->string('category');                   // Channel (Pending Payment, Ikman, Facebook-Ads)
            $table->json('member')->nullable();           // Selected members (store user IDs as JSON array)
            $table->unsignedInteger('rule_1_days');       // Days for rule 1
            $table->unsignedInteger('rule_2_days');       // Days for rule 2
            // Extra useful fields for system functionality
            $table->boolean('is_active')->default(true);  // Enable / disable cron rule
            $table->timestamp('last_run_at')->default(now()); // Track last run
            $table->json('last_run_result')->nullable();  // Store summary of last run (processed, failed, etc.)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cron');
    }
};
