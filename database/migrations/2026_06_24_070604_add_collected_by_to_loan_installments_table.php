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
        if (Schema::hasTable('loan_installments') && !Schema::hasColumn('loan_installments', 'collected_by')) {
            Schema::table('loan_installments', function (Blueprint $table) {
                $table->foreignId('collected_by')->nullable()->after('paid_date')->constrained('users')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('loan_installments') && Schema::hasColumn('loan_installments', 'collected_by')) {
            Schema::table('loan_installments', function (Blueprint $table) {
                $table->dropForeign(['collected_by']);
                $table->dropColumn('collected_by');
            });
        }
    }
};
