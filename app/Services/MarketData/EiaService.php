<?php

namespace App\Services\MarketData;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Adapter for the U.S. Energy Information Administration (EIA) API v2.
 * Free API key: https://www.eia.gov/opendata/
 */
class EiaService
{
    private const BASE = 'https://api.eia.gov/v2';

    public function __construct(private readonly string $apiKey) {}

    /**
     * Fetch the N most-recent daily observations for a given route + series.
     *
     * Returns array of ['date' => 'YYYY-MM-DD', 'value' => float] sorted newest-first,
     * or an empty array on failure.
     */
    public function fetchSeries(string $route, string $series, int $limit = 30): array
    {
        if (empty($this->apiKey)) {
            Log::warning('EIA API key not configured — skipping fetch', ['series' => $series]);
            return [];
        }

        $url = self::BASE . '/' . ltrim($route, '/') . '/data/';

        try {
            $response = Http::timeout(15)->get($url, [
                'api_key'                  => $this->apiKey,
                'frequency'                => 'daily',
                'data[0]'                  => 'value',
                'facets[series][]'         => $series,
                'sort[0][column]'          => 'period',
                'sort[0][direction]'       => 'desc',
                'length'                   => $limit,
            ]);

            if ($response->failed()) {
                Log::error('EIA API HTTP error', [
                    'series' => $series,
                    'status' => $response->status(),
                ]);
                return [];
            }

            $rows = $response->json('response.data', []);

            return collect($rows)
                ->filter(fn($r) => isset($r['period'], $r['value']) && is_numeric($r['value']))
                ->map(fn($r) => ['date' => $r['period'], 'value' => (float) $r['value']])
                ->values()
                ->toArray();

        } catch (\Throwable $e) {
            Log::error('EIA API exception', ['series' => $series, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /** Convenience: return only the latest single observation. */
    public function latestValue(string $route, string $series): ?array
    {
        return $this->fetchSeries($route, $series, 1)[0] ?? null;
    }
}
