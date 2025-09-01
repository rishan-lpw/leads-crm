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
        Schema::table('customer', function (Blueprint $table) {
            $table->string('membership_status')->default('inactive')->after('role_id');
            $table->string('membership_category')->default('basic')->after('membership_status');
            $table->date('payment_exp_date')->nullable()->after('membership_category');
            $table->date('membership_exp_date')->nullable()->after('payment_exp_date');
            $table->integer('available_boosts_source')->default(0)->after('membership_exp_date');
            $table->date('last_boost_added_date')->nullable()->after('available_boosts_source');
            $table->string('ad_url')->nullable()->after('last_boost_added_date');
            $table->text('customer_remarks')->nullable()->after('ad_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer', function (Blueprint $table) {
            $table->dropColumn([
                'membership_status',
                'membership_category',
                'payment_exp_date',
                'membership_exp_date',
                'available_boosts_source',
                'last_boost_added_date',
                'ad_url',
                'customer_remarks'
            ]);
        });
    }
};
