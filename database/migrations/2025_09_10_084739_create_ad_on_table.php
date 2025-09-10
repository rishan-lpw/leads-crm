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
        Schema::create('ad_on', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('desc_short', 255)->nullable();
            $table->text('description')->nullable();
            $table->text('order_desc')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('discount_price', 15, 2)->default(0);
            $table->string('valid_period', 100)->nullable();
            $table->string('image', 255)->nullable();
            $table->string('offer', 100)->nullable();
            $table->string('site', 100)->nullable();
            $table->string('active_method', 100)->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_on');
    }
};
