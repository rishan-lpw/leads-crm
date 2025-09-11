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
        Schema::create('customer', function (Blueprint $table) {
            $table->id(); // maps to API "uid"

            // From API
            $table->string('firstname')->nullable();
            $table->string('surname')->nullable();
            $table->string('mobile')->nullable();          // maps to "mobile"
            $table->string('mobile_alt')->nullable();      // maps to "mobile_alt"
            $table->string('email')->nullable();

            // CRM-specific
            $table->string('address')->nullable();
            $table->unsignedBigInteger('add_id')->nullable();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->date('membership_exp_date')->nullable();
            $table->date('payment_exp_date')->nullable();
            $table->string('membership_status')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer');
    }
};
