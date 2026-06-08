<?php

namespace Database\Seeders;

use App\Models\IndexDefinition;
use Illuminate\Database\Seeder;

/**
 * Wires each seeded IndexDefinition to a live data source.
 * Run after MasterDataSeeder: php artisan db:seed --class=MarketDataFeedSeeder
 *
 * Sources used:
 *   eia  — U.S. EIA API v2 (free key: https://www.eia.gov/opendata/)
 *   fred — FRED API (free key: https://fred.stlouisfed.org/docs/api/api_key.html)
 *
 * TTF / NBP are proxied via Henry Hub (FRED:DHHNGSP) with a configurable
 * multiplier. European gas trades at a premium to Henry Hub (~1.2–2x depending
 * on the period). Adjust live_feed_multiplier as market conditions change.
 */
class MarketDataFeedSeeder extends Seeder
{
    public function run(): void
    {
        $feeds = [
            // ── Crude Oil ─────────────────────────────────────────────────────
            'Brent 1M' => [
                'live_feed_source'     => 'eia',
                'live_feed_route'      => 'petroleum/pri/spt/data',
                'live_feed_series'     => 'RBRTE',
                'live_feed_multiplier' => 1.0,  // USD/BBL — matches index UOM
            ],
            'WTI 1M' => [
                'live_feed_source'     => 'eia',
                'live_feed_route'      => 'petroleum/pri/spt/data',
                'live_feed_series'     => 'RWTC',
                'live_feed_multiplier' => 1.0,  // USD/BBL
            ],

            // ── Natural Gas ───────────────────────────────────────────────────
            // Henry Hub spot (FRED) is the world's benchmark reference.
            // TTF day-ahead runs at a premium; the multiplier acts as a static
            // basis spread until a real European feed is connected.
            'TTF Day-Ahead' => [
                'live_feed_source'     => 'fred',
                'live_feed_route'      => null,
                'live_feed_series'     => 'DHHNGSP',  // Henry Hub, $/MMBTU daily
                'live_feed_multiplier' => 1.35,        // ~TTF basis to HH (adjust seasonally)
            ],
            'NBP Day-Ahead' => [
                'live_feed_source'     => 'fred',
                'live_feed_route'      => null,
                'live_feed_series'     => 'DHHNGSP',
                'live_feed_multiplier' => 1.25,        // ~NBP basis to HH
            ],

            // ── Power ────────────────────────────────────────────────────────
            // UK baseload estimated from TTF (gas-at-the-margin heuristic).
            // TTF ($/MMBTU) × heat-rate (8 MMBTU/MWh) × efficiency factor.
            // Replace with a real N2EX/EPEX feed when available.
            'UK Power Baseload' => [
                'live_feed_source'     => 'fred',
                'live_feed_route'      => null,
                'live_feed_series'     => 'DHHNGSP',
                'live_feed_multiplier' => 10.8,  // HH $/MMBTU → est. UK power $/MWh
            ],
        ];

        foreach ($feeds as $indexName => $config) {
            $updated = IndexDefinition::where('index_name', $indexName)->update($config);
            if ($updated) {
                $this->command->line("  ✓ {$indexName} → {$config['live_feed_source']}:{$config['live_feed_series']}");
            } else {
                $this->command->warn("  ! Index not found: {$indexName}");
            }
        }

        $this->command->info('MarketDataFeedSeeder complete.');
    }
}
