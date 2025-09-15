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
        Schema::create('activity', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('lead_id')->unsigned();
            $table->string('activity_type', 100);
            $table->text('notes')->nullable();
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('due_at')->nullable();
            $table->dateTime('last_checked_at')->nullable();
            $table->bigInteger('activity_follow_up_id')->unsigned()->nullable();
            $table->string('action', 100)->nullable();
            $table->integer('qty')->nullable();
            $table->decimal('value', 10, 2)->nullable();
            $table->uuid('ad_id')->nullable();
            $table->text('comments')->nullable();
            $table->dateTime('date_time')->nullable();
            $table->bigInteger('assigned_by')->unsigned()->nullable();
            $table->string('old_am', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity');
    }
};
