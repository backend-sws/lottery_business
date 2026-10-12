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
        if (!Schema::hasTable('loan_requests')) {
            Schema::create('loan_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->decimal('amount', 15, 2);
                $table->integer('duration_months');
                $table->string('payment_frequency')->default('monthly'); // daily, weekly, monthly
                $table->text('purpose')->nullable();
                $table->string('status')->default('pending'); // pending, approved, rejected
                $table->text('admin_remarks')->nullable();
                $table->foreignId('approved_loan_id')->nullable()->constrained('loans')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_requests');
    }
};
