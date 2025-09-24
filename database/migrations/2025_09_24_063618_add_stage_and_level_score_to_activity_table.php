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
        Schema::table('activity', function (Blueprint $table) {
            $table->string('stage')->nullable()->after('activity_type');
            $table->integer('level_score')->nullable()->after('stage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity', function (Blueprint $table) {
            $table->dropColumn(['stage', 'level_score']);
        });
    }
};
