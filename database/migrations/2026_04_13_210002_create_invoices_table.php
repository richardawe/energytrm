<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 20)->unique(); // INV-2026-0001
            $table->foreignId('trade_id')->constrained('trades');
            $table->foreignId('counterparty_id')->constrained('parties');

            $table->date('invoice_date');
            $table->date('due_date')->nullable();

            // Amount (calculated: qty × price)
            $table->decimal('invoice_amount', 18, 2);
            $table->foreignId('currency_id')->constrained('currencies');
            $table->foreignId('payment_terms_id')->nullable()->constrained('payment_terms');

            $table->enum('invoice_status', ['Draft', 'Issued', 'Paid', 'Overdue', 'Cancelled'])
                  ->default('Draft');

            $table->text('comments')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
