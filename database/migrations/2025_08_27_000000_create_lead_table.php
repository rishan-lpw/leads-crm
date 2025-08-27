<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_type_id');
            $table->string('name');
            $table->date('posted_date')->nullable();
            $table->string('source')->nullable();
            $table->string('am')->nullable();
            $table->string('status')->nullable();
            $table->text('latest_comments')->nullable();
            $table->string('last_update_by');
            $table->string('tel')->nullable();
            $table->decimal('price', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead');
    }
};
