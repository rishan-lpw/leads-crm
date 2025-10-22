<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		// Add score to lead table - float value
		Schema::table('lead', function (Blueprint $table) {
			$table->float('score')->default(0)->after('is_active');
		});
	}

	public function down(): void
	{
		Schema::table('lead', function (Blueprint $table) {
			$table->dropColumn('score');
		});
	}
};


