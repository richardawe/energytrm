<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->id();

            // Three-ID behaviour
            $table->string('deal_number', 20)->unique();        // DL-2026-0001, permanent
            $table->string('transaction_number', 20)->unique(); // TXN-2026-0001, regenerated on amendment
            $table->string('instrument_number', 20)->index();   // INST-2026-0001, shared across OTC pair
            $table->unsignedTinyInteger('version')->default(1); // increments on amendment

            // Status & type
            $table->enum('trade_status', ['Pending', 'Validated', 'Settled'])->default('Pending');
            $table->date('trade_date');

            // Direction
            $table->enum('buy_sell', ['Buy', 'Sell']);
            $table->enum('pay_rec', ['Pay', 'Receive']); // derived from buy_sell

            // Dates
            $table->date('start_date');
            $table->date('end_date');

            // Internal side
            $table->foreignId('internal_bu_id')->constrained('parties');
            $table->foreignId('portfolio_id')->constrained('portfolios');

            // External side
            $table->foreignId('counterparty_id')->constrained('parties');

            // Product & quantity
            $table->foreignId('product_id')->constrained('products');
            $table->decimal('quantity', 15, 4);
            $table->enum('volume_type', ['Fixed', 'Variable', 'Optional'])->default('Fixed');
            $table->foreignId('uom_id')->constrained('uoms');

            // Pricing
            $table->enum('fixed_float', ['Fixed', 'Float'])->default('Fixed');
            $table->foreignId('index_id')->nullable()->constrained('index_definitions');
            $table->decimal('fixed_price', 15, 6)->nullable();
            $table->decimal('spread', 15, 6)->nullable()->default(0);

            // Settlement
            $table->foreignId('currency_id')->constrained('currencies');
            $table->foreignId('payment_terms_id')->nullable()->constrained('payment_terms');

            // Logistics
            $table->string('incoterm_code', 10)->nullable();
            $table->string('load_port', 100)->nullable();
            $table->string('discharge_port', 100)->nullable();

            // Optional links
            $table->foreignId('broker_id')->nullable()->constrained('brokers');
            $table->foreignId('agreement_id')->nullable()->constrained('agreements');
            $table->text('comments')->nullable();

            // Audit
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('validated_by')->nullable()->constrained('users');
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};
