<?php

namespace App\Http\Controllers\Risk;

use App\Http\Controllers\Controller;
use App\Models\FinancialTrade;
use App\Models\IndexDefinition;
use App\Models\Trade;

class VarController extends Controller
{
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
        // Physical float trades
        $floatTrades = Trade::with(['index.gridPoints', 'uom', 'product', 'currency'])
            ->whereIn('trade_status', ['Pending', 'Validated'])
            ->where('fixed_float', 'Float')
            ->whereNotNull('index_id')
            ->get();

        // Financial swap (float leg) and futures trades with a price index
        $finFloatTrades = FinancialTrade::with([
            'floatIndex.gridPoints', 'futuresIndex.gridPoints', 'product', 'currency',
        ])->whereIn('trade_status', ['Pending', 'Validated', 'Active', 'Open'])
          ->whereIn('instrument_type', ['swap', 'futures'])
          ->get();

        // ── Historical VaR (physical + financial combined) ────────────────────
        $scenarioPnls = $this->buildHistoricalScenarios($floatTrades, $finFloatTrades);

        $var95 = $var99 = null;
        $scenarioCount = count($scenarioPnls);
        $minDataPoints = 30;

        if ($scenarioCount >= $minDataPoints) {
            sort($scenarioPnls);
            $var95 = abs($scenarioPnls[(int) floor(0.05 * $scenarioCount)]);
            $var99 = abs($scenarioPnls[(int) floor(0.01 * $scenarioCount)]);
        }

        // ── Stress Tests ──────────────────────────────────────────────────────
        $stressResults = $this->runStressTests($floatTrades, $finFloatTrades);

        // ── Summary ───────────────────────────────────────────────────────────
        $fixedTradeCount = Trade::whereIn('trade_status', ['Pending', 'Validated'])
            ->where('fixed_float', 'Fixed')->count();

        $summary = [
            'float_trade_count'     => $floatTrades->count(),
            'fixed_trade_count'     => $fixedTradeCount,
            'fin_float_trade_count' => $finFloatTrades->count(),
            'scenario_count'        => $scenarioCount,
            'min_data_points'       => $minDataPoints,
        ];

        return view('risk.var', compact(
            'var95', 'var99', 'stressResults', 'summary', 'scenarioCount', 'minDataPoints'
        ));
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function buildHistoricalScenarios($floatTrades, $finFloatTrades): array
    {
        // Build index→returns map (physical trades use index_id)
        $byIndex = $floatTrades->groupBy('index_id');

        // Financial swaps use float_index_id; futures use futures_index_id
        $finByIndex = collect();
        foreach ($finFloatTrades as $ft) {
            $idxId = $ft->instrument_type === 'swap'
                ? $ft->float_index_id
                : $ft->futures_index_id;
            if ($idxId) {
                $finByIndex->push(['trade' => $ft, 'index_id' => $idxId]);
            }
        }
        $finGrouped = $finByIndex->groupBy('index_id');

        $allIndexIds = $byIndex->keys()->merge($finGrouped->keys())->unique();

        $indexReturns = [];
        foreach ($allIndexIds as $indexId) {
            $index = IndexDefinition::with('gridPoints')->find($indexId);
            if (!$index) continue;
            $points = $index->gridPoints->sortBy('price_date')->values()
                ->map(fn($p) => (float) $p->price)->toArray();
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

        $minLen = min(array_map('count', $indexReturns));
        if ($minLen < 1) return [];

        $scenarioPnls = [];
        for ($s = 0; $s < $minLen; $s++) {
            $pnl = 0.0;
            // Physical float trades
            foreach ($byIndex as $indexId => $trades) {
                if (!isset($indexReturns[$indexId])) continue;
                $r = $indexReturns[$indexId][count($indexReturns[$indexId]) - $minLen + $s] ?? 0;
                foreach ($trades as $trade) {
                    $price     = (float) ($trade->index?->latestPrice?->price ?? 0);
                    $direction = $trade->buy_sell === 'Buy' ? 1 : -1;
                    $pnl      += $r * $price * (float) $trade->quantity * $direction;
                }
            }
            // Financial float/futures trades
            foreach ($finGrouped as $indexId => $items) {
                if (!isset($indexReturns[$indexId])) continue;
                $r = $indexReturns[$indexId][count($indexReturns[$indexId]) - $minLen + $s] ?? 0;
                foreach ($items as $item) {
                    $ft        = $item['trade'];
                    $direction = $ft->buy_sell === 'Buy' ? 1 : -1;
                    if ($ft->instrument_type === 'swap') {
                        $price = (float) ($ft->floatIndex?->latestPrice?->price ?? 0);
                        $pnl  += $r * $price * (float) $ft->notional_quantity * $direction;
                    } else { // futures
                        $price = (float) ($ft->futuresIndex?->latestPrice?->price ?? $ft->futures_price ?? 0);
                        $pnl  += $r * $price * (float) $ft->num_contracts * (float) $ft->contract_size * $direction;
                    }
                }
            }
            $scenarioPnls[] = $pnl;
        }

        return $scenarioPnls;
    }

    private function runStressTests($floatTrades, $finFloatTrades): array
    {
        $results = [];
        foreach (self::STRESS_SCENARIOS as $label => $shockPct) {
            $portfolioPnl = 0.0;
            foreach ($floatTrades as $trade) {
                $price      = (float) ($trade->index?->latestPrice?->price ?? 0);
                $delta      = $price * ($shockPct / 100);
                $direction  = $trade->buy_sell === 'Buy' ? 1 : -1;
                $portfolioPnl += $delta * (float) $trade->quantity * $direction;
            }
            foreach ($finFloatTrades as $ft) {
                $direction = $ft->buy_sell === 'Buy' ? 1 : -1;
                if ($ft->instrument_type === 'swap') {
                    $price        = (float) ($ft->floatIndex?->latestPrice?->price ?? 0);
                    $portfolioPnl += $price * ($shockPct / 100) * (float) $ft->notional_quantity * $direction;
                } else { // futures
                    $price        = (float) ($ft->futuresIndex?->latestPrice?->price ?? $ft->futures_price ?? 0);
                    $portfolioPnl += $price * ($shockPct / 100) * (float) $ft->num_contracts * (float) $ft->contract_size * $direction;
                }
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
