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
        Schema::table('agent_collections', function (Blueprint $table) {
            if (!Schema::hasColumn('agent_collections', 'payment_mode')) {
                $table->string('payment_mode', 20)->default('cash')->after('amount_collected');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_collections', function (Blueprint $table) {
            if (Schema::hasColumn('agent_collections', 'payment_mode')) {
                $table->dropColumn('payment_mode');
            }
        });
    }
};
