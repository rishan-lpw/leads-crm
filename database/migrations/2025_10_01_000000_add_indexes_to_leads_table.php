<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToLeadsTable extends Migration
{
    public function up(): void
    {
        Schema::table('lead', function (Blueprint $table) {
            // add indexes used in queries/filters
            $table->index('user_id');
            $table->index('ad_id');
            $table->index('status');
            $table->index('is_active');
            $table->index('posted_date');
            $table->index('price');
        });
    }

    public function down(): void
    {
        Schema::table('lead', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['ad_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['posted_date']);
            $table->dropIndex(['price']);
        });
    }
}