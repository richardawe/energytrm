<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('financial_trades', function (Blueprint $table) {
            $table->unsignedBigInteger('hedges_physical_trade_id')->nullable()->after('comments');
            $table->foreign('hedges_physical_trade_id')
                  ->references('id')->on('trades')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('financial_trades', function (Blueprint $table) {
            $table->dropForeign(['hedges_physical_trade_id']);
            $table->dropColumn('hedges_physical_trade_id');
        });
    }
};
