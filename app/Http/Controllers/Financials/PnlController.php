<?php

namespace App\Http\Controllers\Financials;

use App\Http\Controllers\Controller;
use App\Models\Trade;
use Illuminate\Http\Request;

class PnlController extends Controller
{
    public function index(Request $request)
    {
        $query = Trade::with([
            'product', 'counterparty', 'currency', 'uom',
            'index.latestPrice', 'invoices.settlements',
        ])->whereIn('trade_status', ['Validated', 'Settled']);

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('currency_id')) {
            $query->where('currency_id', $request->currency_id);
        }

        $trades = $query->orderByDesc('trade_date')->get();

        $rows = $trades->map(function (Trade $trade) {
            $tradePrice  = $this->tradePrice($trade);
            $marketPrice = (float) ($trade->index?->latestPrice?->price ?? $tradePrice);
            $quantity    = (float) $trade->quantity;
            $direction   = $trade->buy_sell === 'Buy' ? 1 : -1;

            $tradeValue     = $quantity * $tradePrice;
            $marketValue    = $quantity * $marketPrice;
            $unrealisedPnl  = ($marketPrice - $tradePrice) * $quantity * $direction;

            // Realised PnL: sum of confirmed settlements vs invoice amount
            $invoiceTotal   = (float) $trade->invoices->sum('invoice_amount');
            $settledTotal   = $trade->invoices->flatMap->settlements
                ->where('settlement_status', 'Confirmed')
                ->sum('payment_amount');
            $realisedPnl    = $trade->trade_status === 'Settled'
                ? ($trade->buy_sell === 'Sell' ? $settledTotal - $invoiceTotal : $invoiceTotal - $settledTotal)
                : null;

            return compact(
                'trade', 'tradePrice', 'marketPrice',
                'tradeValue', 'marketValue', 'unrealisedPnl', 'realisedPnl'
            );
        });

        $totals = [
            'trade_value'    => $rows->sum('tradeValue'),
            'market_value'   => $rows->sum('marketValue'),
            'unrealised_pnl' => $rows->sum('unrealisedPnl'),
            'realised_pnl'   => $rows->whereNotNull('realisedPnl')->sum('realisedPnl'),
        ];

        $products   = \App\Models\Product::orderBy('name')->get();
        $currencies = \App\Models\Currency::where('is_active', true)->orderBy('code')->get();

        return view('financials.pnl.index', compact('rows', 'totals', 'products', 'currencies'));
    }

    private function tradePrice(Trade $trade): float
    {
        if ($trade->fixed_float === 'Fixed') {
            return (float) $trade->fixed_price;
        }
        $latestPrice = (float) ($trade->index?->latestPrice?->price ?? 0);
        return $latestPrice + (float) $trade->spread;
    }
}
