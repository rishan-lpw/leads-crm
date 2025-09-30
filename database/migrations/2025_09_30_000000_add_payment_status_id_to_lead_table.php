<!-- Add a new column into lead table called payment_status_id as the foreign key of payment_status table. 
  -->
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\PaymentStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lead', function (Blueprint $table) {
            // payment_status_id column as foreign key of payment_status table
            $table->unsignedBigInteger('payment_status_id')->nullable()->after('status');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropColumn('payment_status_id');
        });
    }
};