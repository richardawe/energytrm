<?php

namespace App\Http\Controllers\Risk;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Portfolio;
use App\Models\Trade;
use Illuminate\Http\Request;

class PortfolioAnalysisController extends Controller
{
    public function index(Request $request)
    {
        $trades = Trade::with([
            'product', 'uom', 'currency', 'portfolio',
            'index.latestPrice', 'invoices.settlements',
        ])->whereIn('trade_status', ['Pending', 'Validated', 'Settled'])
          ->get();

        // ── Net Position by Portfolio + Product ───────────────────────────────
        $portfolioRows = $trades
            ->groupBy('portfolio_id')
            ->map(function ($group) {
                $portfolio = $group->first()->portfolio;

                $byProduct = $group->groupBy('product_id')->map(function ($pGroup) {
                    $p       = $pGroup->first()->product;
                    $uom     = $pGroup->first()->uom;
                    $netQty  = $pGroup->sum(fn($t) => $t->buy_sell === 'Buy'
                        ? (float) $t->quantity
                        : -(float) $t->quantity);
                    $tradeVal = $pGroup->sum(fn($t) => $this->tradeValue($t));
                    $mtmVal   = $pGroup->sum(fn($t) => $this->mtmValue($t));
                    $unrealPnl = $pGroup->sum(fn($t) => $this->unrealisedPnl($t));

                    return [
                        'product'       => $p,
                        'uom'           => $uom,
                        'net_qty'       => $netQty,
                        'trade_count'   => $pGroup->count(),
                        'trade_value'   => $tradeVal,
                        'mtm_value'     => $mtmVal,
                        'unrealised_pnl'=> $unrealPnl,
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

        // ── Exposure by Currency ──────────────────────────────────────────────
        $byCurrency = $trades
            ->whereIn('trade_status', ['Pending', 'Validated'])
            ->groupBy('currency_id')
            ->map(function ($group) {
                $ccy       = $group->first()->currency;
                $longVal   = $group->where('buy_sell', 'Buy')->sum(fn($t) => $this->tradeValue($t));
                $shortVal  = $group->where('buy_sell', 'Sell')->sum(fn($t) => $this->tradeValue($t));
                return [
                    'currency'   => $ccy,
                    'long_value' => $longVal,
                    'short_value'=> $shortVal,
                    'net_value'  => $longVal - $shortVal,
                ];
            })->values();

        // ── Portfolio totals ──────────────────────────────────────────────────
        $totals = [
            'trade_value'    => $portfolioRows->sum('trade_value'),
            'mtm_value'      => $portfolioRows->sum('mtm_value'),
            'unrealised_pnl' => $portfolioRows->sum('unrealised_pnl'),
        ];

        return view('risk.portfolio-analysis', compact(
            'portfolioRows', 'byCurrency', 'totals'
        ));
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function tradePrice(Trade $trade): float
    {
        if ($trade->fixed_float === 'Fixed') {
            return (float) $trade->fixed_price;
        }
        return (float) ($trade->index?->latestPrice?->price ?? 0)
             + (float) $trade->spread;
    }

    private function tradeValue(Trade $trade): float
    {
        return (float) $trade->quantity * $this->tradePrice($trade);
    }

    private function mtmValue(Trade $trade): float
    {
        $mktPrice = (float) ($trade->index?->latestPrice?->price ?? $this->tradePrice($trade));
        return (float) $trade->quantity * $mktPrice;
    }

    private function unrealisedPnl(Trade $trade): float
    {
        if ($trade->fixed_float !== 'Float') return 0.0;
        $mktPrice   = (float) ($trade->index?->latestPrice?->price ?? 0);
        $tradePrice = $this->tradePrice($trade);
        $direction  = $trade->buy_sell === 'Buy' ? 1 : -1;
        return ($mktPrice - $tradePrice) * (float) $trade->quantity * $direction;
    }
}
