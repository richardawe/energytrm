<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Expand the status enum to include Active and Closed (MySQL only; SQLite uses TEXT)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE trades MODIFY COLUMN trade_status ENUM('Pending','Validated','Active','Settled','Closed') DEFAULT 'Pending'");
        }

        Schema::table('trades', function (Blueprint $table) {
            // Trader (explicitly assignable, distinct from created_by)
            $table->foreignId('trader_id')->nullable()->after('created_by')->constrained('users')->nullOnDelete();

            // Price unit separate from volume UOM (e.g. qty in MT, price in $/MMBTU)
            $table->foreignId('price_unit_id')->nullable()->after('uom_id')->constrained('uoms')->nullOnDelete();

            // Pricing detail fields
            $table->string('reference_source', 50)->nullable()->after('spread')
                  ->comment('Price publisher: Platts, Argus, ICE Settle, etc.');
            $table->enum('put_call', ['Put', 'Call'])->nullable()->after('reference_source')
                  ->comment('For trades with embedded optionality');

            // Pipeline / zone / location delivery path (gas & power primarily)
            $table->foreignId('pipeline_id')->nullable()->after('discharge_port')
                  ->constrained('pipelines')->nullOnDelete();
            $table->foreignId('zone_id')->nullable()->after('pipeline_id')
                  ->constrained('pipeline_zones')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->after('zone_id')
                  ->constrained('pipeline_locations')->nullOnDelete();
            $table->decimal('fuel_percent', 6, 4)->nullable()->after('location_id')
                  ->comment('Approximate pipeline fuel shrinkage %');

            // Physical-financial hedge link
            $table->unsignedBigInteger('hedged_by_financial_trade_id')->nullable()->after('comments');
            $table->foreign('hedged_by_financial_trade_id')
                  ->references('id')->on('financial_trades')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->dropForeign(['trader_id']);
            $table->dropForeign(['price_unit_id']);
            $table->dropForeign(['pipeline_id']);
            $table->dropForeign(['zone_id']);
            $table->dropForeign(['location_id']);
            $table->dropForeign(['hedged_by_financial_trade_id']);
            $table->dropColumn([
                'trader_id', 'price_unit_id', 'reference_source', 'put_call',
                'pipeline_id', 'zone_id', 'location_id', 'fuel_percent',
                'hedged_by_financial_trade_id',
            ]);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE trades MODIFY COLUMN trade_status ENUM('Pending','Validated','Settled') DEFAULT 'Pending'");
        }
    }
};
