<?php

namespace App\Services\MarketData;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Adapter for the FRED (Federal Reserve Economic Data) API.
 * Free API key: https://fred.stlouisfed.org/docs/api/api_key.html
 *
 * Covers: Henry Hub gas, SOFR risk-free rate, and FX rates as fallback.
 */
class FredService
{
    private const BASE = 'https://api.stlouisfed.org/fred';

    public function __construct(private readonly string $apiKey) {}

    /**
     * Fetch the N most-recent observations for a FRED series.
     *
     * Returns array of ['date' => 'YYYY-MM-DD', 'value' => float] sorted newest-first,
     * or empty array on failure. FRED uses "." for missing values; those are filtered out.
     */
    public function fetchSeries(string $seriesId, int $limit = 30): array
    {
        if (empty($this->apiKey)) {
            Log::warning('FRED API key not configured — skipping fetch', ['series' => $seriesId]);
            return [];
        }

        try {
            $response = Http::timeout(15)->get(self::BASE . '/series/observations', [
                'series_id'   => $seriesId,
                'api_key'     => $this->apiKey,
                'file_type'   => 'json',
                'sort_order'  => 'desc',
                'limit'       => $limit,
            ]);

            if ($response->failed()) {
                Log::error('FRED API HTTP error', [
                    'series' => $seriesId,
                    'status' => $response->status(),
                ]);
                return [];
            }

            $observations = $response->json('observations', []);

            return collect($observations)
                ->filter(fn($o) => isset($o['date'], $o['value']) && is_numeric($o['value']))
                ->map(fn($o) => ['date' => $o['date'], 'value' => (float) $o['value']])
                ->values()
                ->toArray();

        } catch (\Throwable $e) {
            Log::error('FRED API exception', ['series' => $seriesId, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /** Return only the latest observation. */
    public function latestValue(string $seriesId): ?array
    {
        return $this->fetchSeries($seriesId, 1)[0] ?? null;
    }

    /**
     * Fetch the current SOFR rate as a decimal (e.g. 0.0530 for 5.30%).
     * Falls back to DFF (Fed Funds Rate) if SOFR is unavailable.
     */
    public function riskFreeRate(): float
    {
        $obs = $this->latestValue('SOFR') ?? $this->latestValue('DFF');
        return $obs ? round((float) $obs['value'] / 100, 6) : 0.05;
    }
}
