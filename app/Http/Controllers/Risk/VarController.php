<?php

namespace App\Http\Controllers\Risk;

use App\Http\Controllers\Controller;
use App\Models\IndexDefinition;
use App\Models\Trade;

class VarController extends Controller
{
    // Stress test shocks applied to market prices (%)
    private const STRESS_SCENARIOS = [
        'Mild Down'    => -5,
        'Moderate Down'=> -10,
        'Severe Down'  => -20,
        'Extreme Down' => -30,
        'Mild Up'      => +5,
        'Moderate Up'  => +10,
        'Severe Up'    => +20,
    ];

    public function index()
    {
        $floatTrades = Trade::with(['index.gridPoints', 'uom', 'product', 'currency'])
            ->whereIn('trade_status', ['Pending', 'Validated'])
            ->where('fixed_float', 'Float')
            ->whereNotNull('index_id')
            ->get();

        // ── Historical VaR ────────────────────────────────────────────────────
        // For each scenario (historical daily return), compute portfolio P&L impact
        $scenarioPnls = $this->buildHistoricalScenarios($floatTrades);

        $var95 = null;
        $var99 = null;
        $scenarioCount = count($scenarioPnls);
        $minDataPoints = 30;

        if ($scenarioCount >= $minDataPoints) {
            sort($scenarioPnls); // ascending: worst first
            $idx95 = (int) floor(0.05 * $scenarioCount);
            $idx99 = (int) floor(0.01 * $scenarioCount);
            $var95 = abs($scenarioPnls[$idx95]);
            $var99 = abs($scenarioPnls[$idx99]);
        }

        // ── Stress Tests ──────────────────────────────────────────────────────
        $stressResults = $this->runStressTests($floatTrades);

        // ── Fixed trades (no market risk) ─────────────────────────────────────
        $fixedTradeCount = Trade::whereIn('trade_status', ['Pending', 'Validated'])
            ->where('fixed_float', 'Fixed')->count();

        // ── Summary ───────────────────────────────────────────────────────────
        $summary = [
            'float_trade_count' => $floatTrades->count(),
            'fixed_trade_count' => $fixedTradeCount,
            'scenario_count'    => $scenarioCount,
            'min_data_points'   => $minDataPoints,
        ];

        return view('risk.var', compact(
            'var95', 'var99', 'stressResults', 'summary', 'scenarioCount', 'minDataPoints'
        ));
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function buildHistoricalScenarios($floatTrades): array
    {
        // Group trades by index
        $byIndex = $floatTrades->groupBy('index_id');

        // For each index, get sorted price history and compute daily returns
        $indexReturns = [];
        foreach ($byIndex->keys() as $indexId) {
            $index  = IndexDefinition::with('gridPoints')->find($indexId);
            if (!$index) continue;

            $points = $index->gridPoints
                ->sortBy('price_date')
                ->values()
                ->map(fn($p) => (float) $p->price)
                ->toArray();

            if (count($points) < 2) continue;

            $returns = [];
            for ($i = 1; $i < count($points); $i++) {
                if ($points[$i - 1] > 0) {
                    $returns[] = ($points[$i] - $points[$i - 1]) / $points[$i - 1];
                }
            }
            $indexReturns[$indexId] = $returns;
        }

        if (empty($indexReturns)) return [];

        // Find the common scenario length (min across all indices)
        $minLen = min(array_map('count', $indexReturns));
        if ($minLen < 1) return [];

        // Use the last $minLen returns from each index (most recent history)
        $scenarioPnls = [];
        for ($s = 0; $s < $minLen; $s++) {
            $pnl = 0.0;
            foreach ($byIndex as $indexId => $trades) {
                if (!isset($indexReturns[$indexId])) continue;
                $returns    = $indexReturns[$indexId];
                $returnIdx  = count($returns) - $minLen + $s;
                $r          = $returns[$returnIdx] ?? 0;

                foreach ($trades as $trade) {
                    $currentPrice = (float) ($trade->index?->latestPrice?->price ?? 0);
                    $qty          = (float) $trade->quantity;
                    $direction    = $trade->buy_sell === 'Buy' ? 1 : -1;
                    $pnl         += $r * $currentPrice * $qty * $direction;
                }
            }
            $scenarioPnls[] = $pnl;
        }

        return $scenarioPnls;
    }

    private function runStressTests($floatTrades): array
    {
        $results = [];
        foreach (self::STRESS_SCENARIOS as $label => $shockPct) {
            $portfolioPnl = 0.0;
            foreach ($floatTrades as $trade) {
                $currentPrice = (float) ($trade->index?->latestPrice?->price ?? 0);
                $shockedPrice = $currentPrice * (1 + $shockPct / 100);
                $priceDelta   = $shockedPrice - $currentPrice;
                $direction    = $trade->buy_sell === 'Buy' ? 1 : -1;
                $portfolioPnl += $priceDelta * (float) $trade->quantity * $direction;
            }
            $results[] = [
                'scenario'   => $label,
                'shock_pct'  => $shockPct,
                'pnl_impact' => $portfolioPnl,
            ];
        }
        return $results;
    }
}
