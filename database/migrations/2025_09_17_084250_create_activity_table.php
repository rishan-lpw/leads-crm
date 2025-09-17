<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    // Fields: id, ,lead_id,	user_id,	action,	qty,	value,	ad_id,	comments,	reminder,	date_time,	by,	old_am,	created_at,	updated_at

    public function up(): void
    {
        Schema::create('activity', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action')->nullable();
            $table->integer('qty')->nullable();
            $table->decimal('value', 10, 2)->nullable();
            $table->unsignedBigInteger('ad_id')->nullable();
            $table->text('comments')->nullable();
            $table->date('reminder')->nullable();
            $table->dateTime('date_time')->nullable();
            $table->string('old_am')->nullable();
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
