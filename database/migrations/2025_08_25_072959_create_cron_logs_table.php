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
        Schema::create('cron_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cron_id');
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->boolean('status')->default(true);  // success/fail
            $table->longText('output')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cron_log');
    }
};
