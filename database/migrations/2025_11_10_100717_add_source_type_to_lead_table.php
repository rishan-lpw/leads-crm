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
        Schema::table('lead', function (Blueprint $table) {
            // Add a column called 'source_type' to store the source type of the lead
            $table->string('source_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead', function (Blueprint $table) {
            // Drop the 'source_type' column
            $table->dropColumn('source_type');
        });
    }
};
