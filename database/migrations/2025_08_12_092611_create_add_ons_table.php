<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    // Add-Ons Table columns:
    // id int pk
    // customer_id int
    // title text
    // description text
    // price float
    // location text
    // type text
    // method_id  - int
    // created_at date
    // updated_at date
    public function up(): void
    {
        Schema::create('add_on', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->float('price')->nullable();
            $table->text('location')->nullable();
            $table->text('type')->nullable();
            $table->unsignedBigInteger('method_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('add_on');
    }
};
