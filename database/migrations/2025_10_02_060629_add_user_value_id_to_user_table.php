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
        Schema::table('user', function (Blueprint $table) {
            // Enable to add multiple user values to the user_value_id column.
            $table->json('user_value_id')->nullable()->after('user_level_id');
            $table->foreign('user_value_id')->references('id')->on('user_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            // Drop user_value_id column
            $table->dropColumn('user_value_id');
        });
    }
};
