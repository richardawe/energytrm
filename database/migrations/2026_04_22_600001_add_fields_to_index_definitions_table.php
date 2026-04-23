<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('index_definitions', function (Blueprint $table) {
            $table->string('index_subgroup', 100)->nullable()->after('index_group');
            $table->string('label', 100)->nullable()->after('index_name');
            $table->string('format', 50)->nullable()->after('class')->comment('Price, DF, Avg Rate, Ratio');
            $table->string('delivery_unit', 50)->nullable()->after('format');
            $table->string('date_sequence', 50)->nullable()->after('delivery_unit');
            $table->string('payment_convention', 50)->nullable()->after('date_sequence');
            $table->date('coverage_end_date')->nullable()->after('payment_convention');
            $table->string('interpolation', 50)->nullable()->after('coverage_end_date')->comment('Back-Step, Front-Step, Linear');
            $table->boolean('inheritance')->default(false)->after('interpolation');
            $table->unsignedBigInteger('discount_index_id')->nullable()->after('inheritance');
            $table->string('reference_source', 100)->nullable()->after('discount_index_id');
            $table->string('projection_method', 100)->nullable()->after('reference_source');
            $table->time('day_start_time')->nullable()->after('projection_method');
            $table->string('holiday_schedule', 100)->nullable()->after('day_start_time');
            $table->string('index_type', 20)->nullable()->after('holiday_schedule')->comment('Standard or Composite');
            $table->string('version_status', 30)->default('Pending')->after('status');
            $table->foreign('discount_index_id')->references('id')->on('index_definitions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('index_definitions', function (Blueprint $table) {
            $table->dropForeign(['discount_index_id']);
            $table->dropColumn([
                'index_subgroup',
                'label',
                'format',
                'delivery_unit',
                'date_sequence',
                'payment_convention',
                'coverage_end_date',
                'interpolation',
                'inheritance',
                'discount_index_id',
                'reference_source',
                'projection_method',
                'day_start_time',
                'holiday_schedule',
                'index_type',
                'version_status',
            ]);
        });
    }
};
