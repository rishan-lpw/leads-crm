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
        Schema::create('lead', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('activity_id')->nullable();
            $table->date('posted_date')->nullable();
            $table->string('source')->nullable();
            // $table->string('am')->nullable();
            $table->string('status')->nullable();
            $table->text('latest_comments')->nullable();
            $table->date('last_update_date')->nullable();
            $table->string('last_update_by');
            $table->string('tel')->nullable();
            $table->decimal('price', 15, 2);
            $table->string('Company_Name')->nullable();
            $table->string('Source_Type')->nullable();
            $table->string('Invoice_Name')->nullable();
            $table->string('Invoice_Address')->nullable();
            $table->string('LinkIn_Profile')->nullable();
            $table->string('Member_Image')->nullable();
            $table->boolean('Auto_Boost')->default(false);
            $table->boolean('Auto_Boost_for_New_Ads')->default(false);
            $table->text('Remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead');
    }
};
