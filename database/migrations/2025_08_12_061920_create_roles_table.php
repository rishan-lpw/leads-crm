<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    // Roles table should include following fields:
    // id int
    // role_name - text
    // description - text
    // assign_date - date
    // assigned_by(user_id) - int
    public function up(): void
    {
        Schema::create('role', function (Blueprint $table) {
            $table->id();
            $table->string('role_name');
            $table->text('description')->nullable();
            $table->date('assign_date');
            $table->foreignId('assigned_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role');
    }
};
