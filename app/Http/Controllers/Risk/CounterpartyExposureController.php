<?php

namespace App\Http\Controllers\Risk;

use App\Http\Controllers\Controller;
use App\Models\FinancialTrade;
use App\Models\Trade;

class CounterpartyExposureController extends Controller
{
    public function index()
    {
        $physicalTrades = Trade::with([
            'counterparty.creditLimitCurrency', 'product', 'currency',
            'index.latestPrice',
        ])->whereIn('trade_status', ['Pending', 'Validated', 'Active'])->get();

        $financialTrades = FinancialTrade::with([
            'counterparty.creditLimitCurrency', 'product', 'currency',
            'floatIndex.latestPrice', 'futuresIndex.latestPrice', 'underlyingIndex.latestPrice',
        ])->whereIn('trade_status', ['Pending', 'Validated', 'Active', 'Open'])->get();

        // Merge all counterparty IDs
        $allCounterpartyIds = $physicalTrades->pluck('counterparty_id')
            ->merge($financialTrades->pluck('counterparty_id'))
            ->unique();

        $physByCp = $physicalTrades->groupBy('counterparty_id');
        $finByCp  = $financialTrades->groupBy('counterparty_id');

        $rows = $allCounterpartyIds->map(function ($cpId) use ($physByCp, $finByCp) {
            $physGroup = $physByCp->get($cpId, collect());
            $finGroup  = $finByCp->get($cpId, collect());
            $party     = ($physGroup->first() ?? $finGroup->first())->counterparty;

            $physExposure = $physGroup->sum(fn($t) => $this->physicalTradeValue($t));
            $finExposure  = $finGroup->sum(fn($t) => $this->financialNotional($t));
            $exposure     = $physExposure + $finExposure;

            $creditLimit = (float) ($party->credit_limit ?? 0);
            $utilisation = $creditLimit > 0 ? ($exposure / $creditLimit) * 100 : null;
            $breached    = $creditLimit > 0 && $exposure > $creditLimit;

            return [
                'party'            => $party,
                'trade_count'      => $physGroup->count() + $finGroup->count(),
                'phys_trade_count' => $physGroup->count(),
                'fin_trade_count'  => $finGroup->count(),
                'exposure'         => $exposure,
                'phys_exposure'    => $physExposure,
                'fin_exposure'     => $finExposure,
                'credit_limit'     => $creditLimit ?: null,
                'utilisation'      => $utilisation,
                'breached'         => $breached,
            ];
        })->sortByDesc('exposure')->values();

        $totalExposure = $rows->sum('exposure');
        $breachCount   = $rows->where('breached', true)->count();

        return view('risk.counterparty-exposure', compact('rows', 'totalExposure', 'breachCount'));
    }

    private function physicalTradeValue(Trade $trade): float
    {
        if ($trade->fixed_float === 'Fixed') {
            return (float) $trade->quantity * (float) $trade->fixed_price;
        }
        $mkt = (float) ($trade->index?->latestPrice?->price ?? 0);
        return (float) $trade->quantity * ($mkt + (float) $trade->spread);
    }

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
