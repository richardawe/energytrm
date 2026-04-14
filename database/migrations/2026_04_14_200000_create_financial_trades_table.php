<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('financial_trades', function (Blueprint $table) {
            $table->id();

            // ── Identity ────────────────────────────────────────────────────
            $table->string('deal_number', 20)->unique();          // FIN-2026-0001
            $table->string('transaction_number', 20)->unique();   // TXN-2026-0002 (shared seq)
            $table->string('instrument_number', 20)->index();     // INST-2026-0002 (shared seq)
            $table->tinyInteger('version')->unsigned()->default(1);

            // ── Instrument type + lifecycle ──────────────────────────────────
            $table->enum('instrument_type', ['swap', 'futures', 'options']);
            $table->enum('trade_status', [
                'Pending', 'Validated', 'Active', 'Open',
                'Settled', 'Closed', 'Expired', 'Exercised',
            ])->default('Pending');
            $table->date('trade_date');

            // ── Common counterparty + book fields ────────────────────────────
            $table->unsignedBigInteger('internal_bu_id');
            $table->unsignedBigInteger('portfolio_id');
            $table->unsignedBigInteger('counterparty_id');
            $table->unsignedBigInteger('currency_id');
            $table->unsignedBigInteger('product_id');
            $table->enum('buy_sell', ['Buy', 'Sell']);
            $table->enum('pay_rec', ['Pay', 'Receive']);
            $table->unsignedBigInteger('broker_id')->nullable();
            $table->unsignedBigInteger('agreement_id')->nullable();
            $table->text('comments')->nullable();

            // ── Swap-specific ────────────────────────────────────────────────
            $table->enum('swap_type', ['commodity', 'basis'])->nullable();
            $table->decimal('fixed_rate', 15, 6)->nullable();
            $table->unsignedBigInteger('float_index_id')->nullable();
            $table->unsignedBigInteger('second_index_id')->nullable();  // basis swap only
            $table->decimal('notional_quantity', 18, 4)->nullable();
            $table->unsignedBigInteger('uom_id')->nullable();
            $table->decimal('spread', 15, 6)->nullable()->default(0);
            $table->enum('payment_frequency', ['Monthly', 'Quarterly'])->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // ── Futures-specific ─────────────────────────────────────────────
            $table->string('exchange', 50)->nullable();
            $table->string('contract_code', 30)->nullable();
            $table->date('expiry_date')->nullable();
            $table->integer('num_contracts')->nullable();
            $table->decimal('contract_size', 15, 4)->nullable();
            $table->decimal('futures_price', 15, 6)->nullable();
            $table->decimal('margin_requirement', 15, 2)->nullable();
            $table->unsignedBigInteger('futures_index_id')->nullable();

            // ── Options-specific ─────────────────────────────────────────────
            $table->enum('option_type', ['call', 'put'])->nullable();
            $table->enum('exercise_style', ['European', 'American'])->nullable();
            $table->decimal('strike_price', 15, 6)->nullable();
            $table->date('option_expiry_date')->nullable();
            $table->decimal('premium', 15, 6)->nullable();
            $table->unsignedBigInteger('underlying_index_id')->nullable();
            $table->decimal('volatility', 8, 6)->nullable();  // stored as decimal e.g. 0.35 = 35%

            // ── Audit ────────────────────────────────────────────────────────
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('validated_by')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();

            $table->foreign('internal_bu_id')->references('id')->on('parties');
            $table->foreign('portfolio_id')->references('id')->on('portfolios');
            $table->foreign('counterparty_id')->references('id')->on('parties');
            $table->foreign('currency_id')->references('id')->on('currencies');
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('broker_id')->references('id')->on('brokers')->nullOnDelete();
            $table->foreign('agreement_id')->references('id')->on('agreements')->nullOnDelete();
            $table->foreign('uom_id')->references('id')->on('uoms')->nullOnDelete();
            $table->foreign('float_index_id')->references('id')->on('index_definitions')->nullOnDelete();
            $table->foreign('second_index_id')->references('id')->on('index_definitions')->nullOnDelete();
            $table->foreign('futures_index_id')->references('id')->on('index_definitions')->nullOnDelete();
            $table->foreign('underlying_index_id')->references('id')->on('index_definitions')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('validated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void { Schema::dropIfExists('financial_trades'); }
};
