<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    // Columns:
    // id
    // activity_id            
    // follow_up_time  
    // status      
    // level_score
    // reminder_at   
    // completed_at  
    // auto_status_updated 
    // created_at
    // updated_at
    public function up(): void
    {
        Schema::create('activity_follow_up', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_id');
            $table->timestamp('follow_up_time')->nullable();
            $table->enum('status', ['pending', 'done', 'missed', 'overdue'])->default('pending');
            $table->integer('level_score')->default(0);
            $table->timestamp('reminder_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->boolean('auto_status_updated')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_follow_up');
    }
};
