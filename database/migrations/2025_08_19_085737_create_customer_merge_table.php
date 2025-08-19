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
        Schema::create('customer_merge', function (Blueprint $table) {
            $table->id();
            $table->foreignId('primary_customer_id');
            $table->foreignId('secondary_customer_id');
            $table->timestamp('merge_at')->nullable();
            $table->foreignId('merged_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_merge');
    }
};
