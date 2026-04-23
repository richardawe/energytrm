<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('index_grid_points', function (Blueprint $table) {
            $table->string('grid_point_label', 100)->nullable()->after('id');
            $table->string('instrument_category', 50)->nullable()->after('grid_point_label')->comment('Swap, Forward-D, Forward-M, Futures-D, Futures-M');
            $table->tinyInteger('priority_level')->nullable()->after('instrument_category')->comment('1 highest, 8 lowest');
            $table->time('start_time')->nullable()->after('priority_level');
            $table->time('end_time')->nullable()->after('start_time');
            $table->decimal('delta_shift', 10, 6)->nullable()->after('end_time');
            $table->string('sensitivity', 20)->nullable()->after('delta_shift')->comment('effective, raw, no');
        });
    }

    public function down(): void
    {
        Schema::table('index_grid_points', function (Blueprint $table) {
            $table->dropColumn([
                'grid_point_label',
                'instrument_category',
                'priority_level',
                'start_time',
                'end_time',
                'delta_shift',
                'sensitivity',
            ]);
        });
    }
};
