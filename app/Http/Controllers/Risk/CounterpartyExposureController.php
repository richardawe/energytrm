<?php

namespace App\Http\Controllers\Risk;

use App\Http\Controllers\Controller;
use App\Models\Party;
use App\Models\Trade;

class CounterpartyExposureController extends Controller
{
    public function index()
    {
        // Active exposure = Pending + Validated trades (not yet settled)
        $trades = Trade::with([
            'counterparty.creditLimitCurrency', 'product', 'currency',
            'index.latestPrice',
        ])->whereIn('trade_status', ['Pending', 'Validated'])->get();

        $rows = $trades
            ->groupBy('counterparty_id')
            ->map(function ($group) {
                $party       = $group->first()->counterparty;
                $exposure    = $group->sum(fn($t) => $this->tradeValue($t));
                $creditLimit = (float) ($party->credit_limit ?? 0);
                $utilisation = $creditLimit > 0 ? ($exposure / $creditLimit) * 100 : null;
                $breached    = $creditLimit > 0 && $exposure > $creditLimit;

                return [
                    'party'        => $party,
                    'trade_count'  => $group->count(),
                    'exposure'     => $exposure,
                    'credit_limit' => $creditLimit ?: null,
                    'utilisation'  => $utilisation,
                    'breached'     => $breached,
                    'trades'       => $group,
                ];
            })
            ->sortByDesc('exposure')
            ->values();

        $totalExposure  = $rows->sum('exposure');
        $breachCount    = $rows->where('breached', true)->count();

        return view('risk.counterparty-exposure', compact('rows', 'totalExposure', 'breachCount'));
    }

    private function tradeValue(Trade $trade): float
    {
        if ($trade->fixed_float === 'Fixed') {
            return (float) $trade->quantity * (float) $trade->fixed_price;
        }
        $mkt = (float) ($trade->index?->latestPrice?->price ?? 0);
        return (float) $trade->quantity * ($mkt + (float) $trade->spread);
    }
}
