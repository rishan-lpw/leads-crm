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
        Schema::create('ad_media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ad_id');
            $table->boolean('has_pic')->default(false);
            $table->integer('pic_count')->default(0);
            $table->text('pics_link')->nullable();
            $table->string('youtube_link')->nullable();
            $table->string('video_link')->nullable();
            $table->string('image_360_link')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_media');
    }
};
