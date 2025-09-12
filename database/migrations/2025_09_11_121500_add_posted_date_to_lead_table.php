<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('lead', 'posted_date')) {
            Schema::table('lead', function (Blueprint $table) {
                $table->date('posted_date')->nullable();
                $table->string('note')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('lead', 'posted_date')) {
            Schema::table('lead', function (Blueprint $table) {
                $table->dropColumn('posted_date');
                $table->dropColumn('note');
            });
        }
    }
};
