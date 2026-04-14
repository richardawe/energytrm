<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('financial_settlements', function (Blueprint $table) {
            $table->id();
            $table->string('settlement_number', 20)->unique();   // FSET-2026-0001
            $table->unsignedBigInteger('financial_trade_id');
            $table->enum('settlement_type', ['periodic', 'final', 'margin', 'premium']);
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->decimal('fixed_leg_amount', 18, 2)->nullable();
            $table->decimal('float_leg_amount', 18, 2)->nullable();
            $table->decimal('net_amount', 18, 2);   // positive = internal BU receives
            $table->date('settlement_date');
            $table->enum('settlement_status', ['Pending', 'Confirmed'])->default('Pending');
            $table->decimal('fx_rate', 12, 6)->default(1.000000);
            $table->string('bank_ref', 100)->nullable();
            $table->text('comments')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('financial_trade_id')->references('id')->on('financial_trades')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    public function down(): void { Schema::dropIfExists('financial_settlements'); }
};
