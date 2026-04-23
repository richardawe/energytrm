<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('financial_trades', function (Blueprint $table) {
            // Settlement
            $table->enum('settlement_method', ['Cash-Settled','Physically-Delivered'])->default('Cash-Settled')->after('instrument_type');
            // Lot size (separate from contract_size/num_contracts for exchange-traded)
            $table->decimal('lot_size', 18, 4)->nullable()->after('contract_size');
            $table->integer('number_of_lots')->nullable()->after('lot_size');
            // Clearing
            $table->string('clearing_venue', 150)->nullable()->after('exchange');
            $table->unsignedBigInteger('clearing_broker_id')->nullable()->after('clearing_venue');
            $table->string('margin_account_ref', 100)->nullable()->after('clearing_broker_id');
            // Hedge designation
            $table->enum('hedge_designation', ['Fair Value Hedge','Cash Flow Hedge','Economic Hedge','Speculative'])->nullable()->after('comments');
            $table->string('hedged_item_reference', 100)->nullable()->after('hedge_designation');

            $table->foreign('clearing_broker_id')->references('id')->on('parties')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('financial_trades', function (Blueprint $table) {
            $table->dropForeign(['clearing_broker_id']);
            $table->dropColumn([
                'settlement_method',
                'lot_size',
                'number_of_lots',
                'clearing_venue',
                'clearing_broker_id',
                'margin_account_ref',
                'hedge_designation',
                'hedged_item_reference',
            ]);
        });
    }
};
