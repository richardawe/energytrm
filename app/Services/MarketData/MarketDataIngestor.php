<?php

namespace App\Services\MarketData;

use App\Models\Currency;
use App\Models\IndexDefinition;
use App\Models\IndexGridPoint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Orchestrates all market data sources, writes prices to index_grid_points,
 * updates currency FX rates, and caches the risk-free rate.
 */
class MarketDataIngestor
{
    public function __construct(
        private readonly EiaService  $eia,
        private readonly FredService $fred,
        private readonly FxRateService $fx,
    ) {}

    // ── Commodity Prices ──────────────────────────────────────────────────────

    /**
     * Sync all index definitions that have a live_feed_source configured.
     * Returns a summary array keyed by index_name.
     */
    public function syncAllPrices(): array
    {
        $results = [];

        $indices = IndexDefinition::whereNotNull('live_feed_source')
            ->where('live_feed_source', '!=', 'manual')
            ->where('rec_status', 'Authorized')
            ->get();

        foreach ($indices as $index) {
            $results[$index->index_name] = $this->syncIndex($index);
        }

        return $results;
    }

    /**
     * Sync a single index definition.
     * Returns ['status' => 'ok'|'skipped'|'error', 'points' => int, 'message' => string].
     */
    public function syncIndex(IndexDefinition $index): array
    {
        $observations = match ($index->live_feed_source) {
            'eia'  => $this->eia->fetchSeries($index->live_feed_route, $index->live_feed_series, 60),
            'fred' => $this->fred->fetchSeries($index->live_feed_series, 60),
            default => [],
        };

        if (empty($observations)) {
            return ['status' => 'error', 'points' => 0, 'message' => 'No data returned from source'];
        }

        $multiplier = (float) ($index->live_feed_multiplier ?: 1.0);
        $inserted   = 0;

        DB::transaction(function () use ($index, $observations, $multiplier, &$inserted) {
            foreach ($observations as $obs) {
                $price = round((float) $obs['value'] * $multiplier, 4);

                IndexGridPoint::updateOrCreate(
                    [
                        'index_id'   => $index->id,
                        'price_date' => $obs['date'],
                    ],
                    ['price' => $price]
                );
                $inserted++;
            }

            $index->update(['last_synced_at' => now()]);
        });

        Log::info('Market data synced', [
            'index'  => $index->index_name,
            'source' => $index->live_feed_source,
            'points' => $inserted,
        ]);

        return ['status' => 'ok', 'points' => $inserted, 'message' => "Synced {$inserted} price points"];
    }

    // ── FX Rates ──────────────────────────────────────────────────────────────

    /**
     * Fetch current FX rates and update the currencies table.
     * Returns count of currencies updated.
     */
    public function syncFxRates(): int
    {
        $rates = $this->fx->fetchRates();
        if (empty($rates)) {
            return 0;
        }

        $updated = 0;
        $currencies = Currency::whereNotIn('code', ['USD'])->get();

        foreach ($currencies as $currency) {
            if (isset($rates[$currency->code]) && is_numeric($rates[$currency->code])) {
                $currency->update([
                    'fx_rate_to_usd' => round((float) $rates[$currency->code], 6),
                    'last_synced_at' => now(),
                ]);
                $updated++;
            }
        }

        Log::info('FX rates synced', ['currencies_updated' => $updated]);
        return $updated;
    }

    // ── Risk-Free Rate ────────────────────────────────────────────────────────

    /**
     * Fetch SOFR from FRED and cache it for 24 hours.
     * The FinancialTrade model reads from Cache::get('risk_free_rate', 0.05).
     */
    public function syncRiskFreeRate(): float
    {
        $rate = $this->fred->riskFreeRate();
        Cache::put('risk_free_rate', $rate, now()->addHours(24));

        Log::info('Risk-free rate synced', ['sofr' => $rate]);
        return $rate;
    }

    // ── Feed Status (for dashboard) ───────────────────────────────────────────

    /**
     * Returns status of all configured feeds for the dashboard widget.
     */
    public function feedStatus(): array
    {
        $indices = IndexDefinition::where('rec_status', 'Authorized')
            ->orderBy('index_name')
            ->get(['id', 'index_name', 'live_feed_source', 'live_feed_series', 'last_synced_at']);

        $currencies = Currency::where('code', '!=', 'USD')
            ->orderBy('code')
            ->get(['code', 'name', 'fx_rate_to_usd', 'last_synced_at']);

        $riskFreeRate = Cache::get('risk_free_rate');

        return [
            'indices'        => $indices,
            'currencies'     => $currencies,
            'risk_free_rate' => $riskFreeRate,
        ];
    }
}
