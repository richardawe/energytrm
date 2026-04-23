<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->time('start_time')->nullable()->after('start_date');
            $table->string('deal_volume_type', 50)->nullable()->after('volume_type');
            $table->string('reset_period', 50)->nullable()->after('fixed_float');
            $table->string('payment_period', 50)->nullable()->after('reset_period');
            $table->integer('payment_date_offset')->nullable()->after('payment_period');
            $table->text('pricing_formula')->nullable()->after('payment_date_offset');
            $table->unsignedBigInteger('transfer_method_id')->nullable()->after('pricing_formula');
            $table->foreign('transfer_method_id')->references('id')->on('transport_classes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->dropForeign(['transfer_method_id']);
            $table->dropColumn([
                'start_time',
                'deal_volume_type',
                'reset_period',
                'payment_period',
                'payment_date_offset',
                'pricing_formula',
                'transfer_method_id',
            ]);
        });
    }
};
