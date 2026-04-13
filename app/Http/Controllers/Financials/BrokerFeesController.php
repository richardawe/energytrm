<?php

namespace App\Http\Controllers\Financials;

use App\Http\Controllers\Controller;
use App\Models\Broker;
use App\Models\Trade;
use Illuminate\Http\Request;

class BrokerFeesController extends Controller
{
    public function index(Request $request)
    {
        $query = Trade::with(['broker.commissions.currency', 'product', 'counterparty', 'currency', 'uom'])
            ->whereNotNull('broker_id')
            ->whereIn('trade_status', ['Validated', 'Settled']);

        if ($request->filled('broker_id')) {
            $query->where('broker_id', $request->broker_id);
        }

        $trades  = $query->orderByDesc('trade_date')->get();
        $brokers = Broker::orderBy('name')->get();

        // Calculate fee per trade
        $rows = $trades->map(function (Trade $trade) {
            $commission = $trade->broker->commissions
                ->where('is_default', true)
                ->first()
                ?? $trade->broker->commissions->first();

            $fee = null;
            if ($commission) {
                $fee = match ($commission->rate_unit) {
                    'per_unit' => (float) $trade->quantity * (float) $commission->commission_rate,
                    'percent'  => $this->tradeValue($trade) * (float) $commission->commission_rate / 100,
                    'flat'     => (float) $commission->commission_rate,
                    default    => null,
                };

                if ($fee !== null) {
                    if ($commission->min_fee !== null) $fee = max($fee, (float) $commission->min_fee);
                    if ($commission->max_fee !== null) $fee = min($fee, (float) $commission->max_fee);
                }
            }

            return [
                'trade'      => $trade,
                'commission' => $commission,
                'fee'        => $fee,
            ];
        });

        $totalFees = $rows->sum('fee');

        return view('financials.broker-fees.index', compact('rows', 'brokers', 'totalFees'));
    }

    private function tradeValue(Trade $trade): float
    {
        if ($trade->fixed_float === 'Fixed') {
            return (float) $trade->quantity * (float) $trade->fixed_price;
        }
        $latestPrice = $trade->index?->latestPrice?->price ?? 0;
        return (float) $trade->quantity * ((float) $latestPrice + (float) $trade->spread);
    }
}
