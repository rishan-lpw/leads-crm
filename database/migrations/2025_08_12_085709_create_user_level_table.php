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
        Schema::create('user_level', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->timestamps();
        });
        
        DB::table('user_level')->insert([
            ['title' => 'Junior User'],
            ['title' => 'Team Lead'],
            ['title' => 'Approver'],
            ['title' => 'Full Access (No Accounts)'],
            ['title' => 'Full Access'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_level');
    }
};
