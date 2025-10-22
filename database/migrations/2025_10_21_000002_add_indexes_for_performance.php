<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::table('activity', function (Blueprint $table) {
			$table->index('lead_id');
			// $table->index('user_id');
			// $table->index('assigned_by');
			$table->index('payment_status_id');
			$table->index('funnel_id');
			// $table->index('created_at');
		});

		Schema::table('lead', function (Blueprint $table) {
			$table->index('user_id');
			$table->index('cust_id');
			// $table->index('status');
			// $table->index('posted_date');
		});

		Schema::table('customer', function (Blueprint $table) {
			$table->index('email');
			// $table->index('mobile');
		});
	}

	public function down(): void
	{
		Schema::table('activity', function (Blueprint $table) {
			$table->dropIndex(['lead_id']);
			// $table->dropIndex(['activity_follow_up_id']);
			// $table->dropIndex(['assigned_by']);
			$table->dropIndex(['payment_status_id']);
			$table->dropIndex(['funnel_id']);
			// $table->dropIndex(['created_at']);
		});

		Schema::table('lead', function (Blueprint $table) {
			$table->dropIndex(['user_id']);
			$table->dropIndex(['cust_id']);
			// $table->dropIndex(['status']);
			// $table->dropIndex(['posted_date']);
		});

		Schema::table('customer', function (Blueprint $table) {
			$table->dropIndex(['email']);
			// $table->dropIndex(['mobile']);
		});
	}
};


