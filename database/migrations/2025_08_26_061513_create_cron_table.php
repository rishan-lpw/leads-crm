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
        Schema::create('cron', function (Blueprint $table) {
            $table->id();
            $table->string('name');                // Job name
            $table->string('command');             // Artisan command name
            $table->string('frequency');           // Cron expression
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->string('category')->option([
                'leads',
                'calls',
                'merge',
                'followup'
            ]);
            
            $table->string('visibility')->default('all')->option([
                'all',
                'seniors',
                'hunters',
                'ams'
            ]);
            $table->text('rules')->nullable()->label('Business Rules');
            $table->string('source_highlight')->option([
                'transaction',
                'pvt-seller'
            ]);
            $table->boolean('allow_manual_trigger')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cron');
    }
};
