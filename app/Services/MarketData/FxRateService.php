<?php

namespace App\Services\MarketData;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * FX rate fetcher using Open Exchange Rates (no API key required).
 * Returns rates as "units of foreign currency per 1 USD" — matching
 * the fx_rate_to_usd column convention in the currencies table.
 *
 * Endpoint: https://open.er-api.com/v6/latest/USD
 */
class FxRateService
{
    private const ENDPOINT = 'https://open.er-api.com/v6/latest/USD';

    /**
     * Returns all current FX rates as ['EUR' => 0.921, 'GBP' => 0.789, ...].
     * Returns empty array on failure.
     */
    public function fetchRates(): array
    {
        try {
            $response = Http::timeout(10)->get(self::ENDPOINT);

            if ($response->failed() || $response->json('result') !== 'success') {
                Log::error('FX rate fetch failed', ['status' => $response->status()]);
                return [];
            }

            return $response->json('rates', []);

        } catch (\Throwable $e) {
            Log::error('FX rate exception', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
