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
        Schema::create('ad_verts', function (Blueprint $table) {
            $table->id();
            $table->string('ad_id', 50)->unique();
            $table->string('cust_id', 50);
            $table->string('type', 50);
            $table->string('property_type', 50);
            $table->string('service_type', 50);
            $table->string('heading', 255);
            $table->text('description');
            $table->timestamp('submit_date')->nullable();
            $table->timestamp('posted_date')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('alt_price', 15, 2)->default(0);
            $table->string('alt_currency', 10)->nullable();
            $table->string('price_type', 50);
            $table->decimal('price_monthly', 15, 2)->default(0);
            $table->decimal('price_sqft', 15, 2)->default(0);
            $table->string('city', 100);
            $table->string('street', 255);
            $table->string('avail', 100);
            $table->string('source', 100);
            $table->string('status', 50)->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_verts');
    }
};
