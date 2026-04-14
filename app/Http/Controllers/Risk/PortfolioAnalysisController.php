<?php

namespace App\Http\Controllers\Risk;

use App\Http\Controllers\Controller;
use App\Models\FinancialTrade;
use App\Models\Trade;

class PortfolioAnalysisController extends Controller
{
    public function index(Request $request)
    {
        $physicalTrades = Trade::with([
            'product', 'uom', 'currency', 'portfolio',
            'index.latestPrice', 'invoices.settlements',
        ])->whereIn('trade_status', ['Pending', 'Validated', 'Settled'])->get();

        $financialTrades = FinancialTrade::with([
            'product', 'uom', 'currency', 'portfolio',
            'floatIndex.latestPrice', 'secondIndex.latestPrice',
            'futuresIndex.latestPrice', 'underlyingIndex.latestPrice',
        ])->whereIn('trade_status', ['Pending', 'Validated', 'Active', 'Open', 'Settled'])->get();

        // ── Net Position by Portfolio (physical only — qty-based) ─────────────
        $portfolioRows = $physicalTrades
            ->groupBy('portfolio_id')
            ->map(function ($group) {
                $portfolio = $group->first()->portfolio;
                $byProduct = $group->groupBy('product_id')->map(function ($pGroup) {
                    $netQty    = $pGroup->sum(fn($t) => $t->buy_sell === 'Buy' ? (float) $t->quantity : -(float) $t->quantity);
                    $tradeVal  = $pGroup->sum(fn($t) => $this->physicalTradeValue($t));
                    $mtmVal    = $pGroup->sum(fn($t) => $this->physicalMtmValue($t));
                    $unrealPnl = $pGroup->sum(fn($t) => $this->physicalUnrealisedPnl($t));
                    return [
                        'product'        => $pGroup->first()->product,
                        'uom'            => $pGroup->first()->uom,
                        'net_qty'        => $netQty,
                        'trade_count'    => $pGroup->count(),
                        'trade_value'    => $tradeVal,
                        'mtm_value'      => $mtmVal,
                        'unrealised_pnl' => $unrealPnl,
                        'type'           => 'physical',
                    ];
                })->values();
                return [
                    'portfolio'      => $portfolio,
                    'products'       => $byProduct,
                    'trade_value'    => $byProduct->sum('trade_value'),
                    'mtm_value'      => $byProduct->sum('mtm_value'),
                    'unrealised_pnl' => $byProduct->sum('unrealised_pnl'),
                ];
            })->values();

        // ── Financial Instrument MTM summary ─────────────────────────────────
        $financialMtm = [
            'swap_mtm'          => $financialTrades->where('instrument_type', 'swap')->sum(fn($t) => $t->swapMtm()),
            'futures_pnl'       => $financialTrades->where('instrument_type', 'futures')->sum(fn($t) => $t->futuresUnrealisedPnl()),
            'options_intrinsic' => $financialTrades->where('instrument_type', 'options')->sum(fn($t) => $t->intrinsicValue()),
            'options_timevalue' => $financialTrades->where('instrument_type', 'options')->sum(fn($t) => $t->timeValue()),
            'trade_count'       => $financialTrades->count(),
        ];

        // ── Exposure by Currency (physical + financial combined) ──────────────
        $allActive = $physicalTrades->whereIn('trade_status', ['Pending', 'Validated']);
        $finActive = $financialTrades->whereIn('trade_status', ['Pending', 'Validated', 'Active', 'Open']);

        $physCcyGroups = $allActive->groupBy('currency_id');
        $finCcyGroups  = $finActive->groupBy('currency_id');
        $allCcyIds     = $physCcyGroups->keys()->merge($finCcyGroups->keys())->unique();

        $byCurrency = $allCcyIds->map(function ($ccyId) use ($physCcyGroups, $finCcyGroups, $physicalTrades, $financialTrades) {
            $physGroup = $physCcyGroups->get($ccyId, collect());
            $finGroup  = $finCcyGroups->get($ccyId, collect());
            $anyTrade  = $physGroup->first() ?? $finGroup->first();
            $ccy       = $anyTrade->currency;

            $longVal  = $physGroup->where('buy_sell', 'Buy')->sum(fn($t) => $this->physicalTradeValue($t))
                      + $finGroup->where('buy_sell', 'Buy')->sum(fn($t) => $this->financialNotional($t));
            $shortVal = $physGroup->where('buy_sell', 'Sell')->sum(fn($t) => $this->physicalTradeValue($t))
                      + $finGroup->where('buy_sell', 'Sell')->sum(fn($t) => $this->financialNotional($t));

            return [
                'currency'    => $ccy,
                'long_value'  => $longVal,
                'short_value' => $shortVal,
                'net_value'   => $longVal - $shortVal,
            ];
        })->values();

        // ── Totals ────────────────────────────────────────────────────────────
        $totals = [
            'trade_value'    => $portfolioRows->sum('trade_value'),
            'mtm_value'      => $portfolioRows->sum('mtm_value'),
            'unrealised_pnl' => $portfolioRows->sum('unrealised_pnl'),
        ];

        return view('risk.portfolio-analysis', compact(
            'portfolioRows', 'byCurrency', 'totals', 'financialMtm'
        ));
    }

    // ── Physical trade helpers ────────────────────────────────────────────────

    private function physicalTradePrice(Trade $trade): float
    {
        if ($trade->fixed_float === 'Fixed') return (float) $trade->fixed_price;
        return (float) ($trade->index?->latestPrice?->price ?? 0) + (float) $trade->spread;
    }

    private function physicalTradeValue(Trade $trade): float
    {
        return (float) $trade->quantity * $this->physicalTradePrice($trade);
    }

    private function physicalMtmValue(Trade $trade): float
    {
        $mkt = (float) ($trade->index?->latestPrice?->price ?? $this->physicalTradePrice($trade));
        return (float) $trade->quantity * $mkt;
    }

    private function physicalUnrealisedPnl(Trade $trade): float
    {
        if ($trade->fixed_float !== 'Float') return 0.0;
        $mkt       = (float) ($trade->index?->latestPrice?->price ?? 0);
        $direction = $trade->buy_sell === 'Buy' ? 1 : -1;
        return ($mkt - $this->physicalTradePrice($trade)) * (float) $trade->quantity * $direction;
    }

    // ── Financial trade helpers ───────────────────────────────────────────────

    private function financialNotional(FinancialTrade $trade): float
    {
        return match ($trade->instrument_type) {
            'swap'    => (float) $trade->notional_quantity * (float) $trade->fixed_rate,
            'futures' => (float) $trade->num_contracts * (float) $trade->contract_size * (float) $trade->futures_price,
            'options' => (float) $trade->premium,
            default   => 0.0,
        };
    }
}
