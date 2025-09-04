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
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('customer_id');
            $table->date('posted_date')->nullable();
            $table->string('source')->nullable();
            // $table->string('am')->nullable();
            $table->string('status')->nullable();
            $table->text('latest_comments')->nullable();
            $table->date('last_update_date')->nullable();
            $table->string('last_update_by');
            $table->string('tel')->nullable();
            $table->decimal('price', 15, 2);
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
