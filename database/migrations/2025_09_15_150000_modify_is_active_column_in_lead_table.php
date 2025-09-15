<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lead', function (Blueprint $table) {
            // Change is_active from tinyint(1) to tinyint to allow 0,1,2,3
            $table->tinyInteger('is_active')->default(1)->change();
        });
    }

    public function down(): void
    {
        Schema::table('lead', function (Blueprint $table) {
            // Revert back to boolean (tinyint(1))
            $table->boolean('is_active')->default(false)->change();
        });
    }
};