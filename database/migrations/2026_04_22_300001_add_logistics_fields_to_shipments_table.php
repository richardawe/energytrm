<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->string('vessel_eta')->nullable()->after('vessel_name');
            $table->date('vessel_eta_date')->nullable()->after('vessel_eta');
            $table->date('laycan_start')->nullable()->after('bl_date');
            $table->date('laycan_end')->nullable()->after('laycan_start');
            $table->datetime('nor_date')->nullable()->after('laycan_end');
            $table->datetime('laytime_commencement')->nullable()->after('nor_date');
            $table->decimal('allowed_laytime_hours', 8, 2)->nullable()->after('laytime_commencement');
            $table->decimal('time_used_hours', 8, 2)->nullable()->after('allowed_laytime_hours');
            $table->decimal('demurrage_rate', 18, 2)->nullable()->after('time_used_hours');
            $table->string('demurrage_currency', 10)->nullable()->after('demurrage_rate');
            $table->decimal('demurrage_amount', 18, 2)->nullable()->after('demurrage_currency');
            $table->decimal('freight_cost', 18, 2)->nullable()->after('demurrage_amount');
            $table->enum('freight_basis', ['Lump Sum', 'Per MT', 'Per BBL', 'Per MMBTU'])->nullable()->after('freight_cost');
            $table->decimal('bl_quantity', 18, 4)->nullable()->after('qty_loaded');
            $table->decimal('draft_survey_quantity', 18, 4)->nullable()->after('bl_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn([
                'vessel_eta', 'vessel_eta_date',
                'laycan_start', 'laycan_end',
                'nor_date', 'laytime_commencement',
                'allowed_laytime_hours', 'time_used_hours',
                'demurrage_rate', 'demurrage_currency', 'demurrage_amount',
                'freight_cost', 'freight_basis',
                'bl_quantity', 'draft_survey_quantity',
            ]);
        });
    }
};
