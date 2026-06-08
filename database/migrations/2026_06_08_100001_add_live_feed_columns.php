<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('index_definitions', function (Blueprint $table) {
            $table->string('live_feed_source')->nullable()->after('rec_status')
                  ->comment('eia | fred | worldbank | manual');
            $table->string('live_feed_route')->nullable()->after('live_feed_source')
                  ->comment('API sub-path (EIA) or series ID (FRED/World Bank)');
            $table->string('live_feed_series')->nullable()->after('live_feed_route')
                  ->comment('Series/facet ID within the route');
            $table->decimal('live_feed_multiplier', 12, 6)->default(1)->after('live_feed_series')
                  ->comment('Unit conversion factor applied to raw API value');
            $table->timestamp('last_synced_at')->nullable()->after('live_feed_multiplier');
        });

        Schema::table('currencies', function (Blueprint $table) {
            $table->timestamp('last_synced_at')->nullable()->after('fx_rate_to_usd');
        });
    }

    public function down(): void
    {
        Schema::table('index_definitions', function (Blueprint $table) {
            $table->dropColumn([
                'live_feed_source', 'live_feed_route', 'live_feed_series',
                'live_feed_multiplier', 'last_synced_at',
            ]);
        });
        Schema::table('currencies', function (Blueprint $table) {
            $table->dropColumn('last_synced_at');
        });
    }
};
