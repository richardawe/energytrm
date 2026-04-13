<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settlements', function (Blueprint $table) {
            $table->id();
            $table->string('settlement_number', 20)->unique(); // SET-2026-0001
            $table->foreignId('invoice_id')->constrained('invoices');

            $table->decimal('payment_amount', 18, 2);
            $table->date('payment_date');
            $table->decimal('fx_rate', 12, 6)->default(1.000000);
            $table->string('bank_ref', 100)->nullable();

            $table->enum('settlement_status', ['Pending', 'Confirmed'])->default('Pending');

            $table->text('comments')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settlements');
    }
};
