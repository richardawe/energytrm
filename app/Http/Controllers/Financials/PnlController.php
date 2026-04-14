<?php

namespace App\Http\Controllers\Financials;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\FinancialTrade;
use App\Models\Product;
use App\Models\Trade;
use Illuminate\Http\Request;

class PnlController extends Controller
{
    public function index(Request $request)
    {
        // ── Physical trades ───────────────────────────────────────────────────
        $physQuery = Trade::with([
            'product', 'counterparty', 'currency', 'uom',
            'index.latestPrice', 'invoices.settlements',
        ])->whereIn('trade_status', ['Validated', 'Settled']);

        if ($request->filled('product_id'))  $physQuery->where('product_id',  $request->product_id);
        if ($request->filled('currency_id')) $physQuery->where('currency_id', $request->currency_id);

        $physRows = $physQuery->orderByDesc('trade_date')->get()->map(function (Trade $trade) {
            $tradePrice  = $this->physicalTradePrice($trade);
            $marketPrice = (float) ($trade->index?->latestPrice?->price ?? $tradePrice);
            $quantity    = (float) $trade->quantity;
            $direction   = $trade->buy_sell === 'Buy' ? 1 : -1;

            $tradeValue    = $quantity * $tradePrice;
            $marketValue   = $quantity * $marketPrice;
            $unrealisedPnl = ($marketPrice - $tradePrice) * $quantity * $direction;

            $invoiceTotal = (float) $trade->invoices->sum('invoice_amount');
            $settledTotal = $trade->invoices->flatMap->settlements
                ->where('settlement_status', 'Confirmed')->sum('payment_amount');
            $realisedPnl  = $trade->trade_status === 'Settled'
                ? ($trade->buy_sell === 'Sell' ? $settledTotal - $invoiceTotal : $invoiceTotal - $settledTotal)
                : null;

            return [
                'kind'          => 'physical',
                'trade'         => $trade,
                'label'         => $trade->deal_number,
                'instrument'    => 'Physical',
                'tradePrice'    => $tradePrice,
                'marketPrice'   => $marketPrice,
                'tradeValue'    => $tradeValue,
                'marketValue'   => $marketValue,
                'unrealisedPnl' => $unrealisedPnl,
                'realisedPnl'   => $realisedPnl,
            ];
        });

        // ── Financial trades ──────────────────────────────────────────────────
        $finQuery = FinancialTrade::with([
            'product', 'counterparty', 'currency',
            'floatIndex.latestPrice', 'secondIndex.latestPrice',
            'futuresIndex.latestPrice', 'underlyingIndex.latestPrice',
            'settlements',
        ])->whereIn('trade_status', ['Active', 'Open', 'Settled', 'Exercised']);

        if ($request->filled('product_id'))  $finQuery->where('product_id',  $request->product_id);
        if ($request->filled('currency_id')) $finQuery->where('currency_id', $request->currency_id);

        $finRows = $finQuery->orderByDesc('trade_date')->get()->map(function (FinancialTrade $trade) {
            [$tradeValue, $marketValue, $unrealisedPnl] = match ($trade->instrument_type) {
                'swap' => [
                    $trade->fixedLegValue(),
                    $trade->floatLegValue(),
                    $trade->swapMtm(),
                ],
                'futures' => [
                    (float) $trade->num_contracts * (float) $trade->contract_size * (float) $trade->futures_price,
                    (float) $trade->num_contracts * (float) $trade->contract_size * $trade->currentFuturesPrice(),
                    $trade->futuresUnrealisedPnl(),
                ],
                'options' => [
                    (float) $trade->premium,
                    $trade->intrinsicValue() + $trade->timeValue(),
                    ($trade->intrinsicValue() + $trade->timeValue()) - (float) $trade->premium,
                ],
                default => [0, 0, 0],
            };

            $settledTotal = $trade->settlements->where('settlement_status', 'Confirmed')->sum('net_amount');
            $realisedPnl  = in_array($trade->trade_status, ['Settled', 'Exercised'])
                ? $settledTotal : null;

            return [
                'kind'          => 'financial',
                'trade'         => $trade,
                'label'         => $trade->deal_number,
                'instrument'    => ucfirst($trade->instrument_type),
                'tradePrice'    => null,
                'marketPrice'   => null,
                'tradeValue'    => $tradeValue,
                'marketValue'   => $marketValue,
                'unrealisedPnl' => $unrealisedPnl,
                'realisedPnl'   => $realisedPnl,
            ];
        });

        $rows = $physRows->merge($finRows);

        $totals = [
            'trade_value'    => $rows->sum('tradeValue'),
            'market_value'   => $rows->sum('marketValue'),
            'unrealised_pnl' => $rows->sum('unrealisedPnl'),
            'realised_pnl'   => $rows->whereNotNull('realisedPnl')->sum('realisedPnl'),
        ];

        $products   = Product::orderBy('name')->get();
        $currencies = Currency::where('is_active', true)->orderBy('code')->get();

        return view('financials.pnl.index', compact('rows', 'totals', 'products', 'currencies'));
    }

    private function physicalTradePrice(Trade $trade): float
    {
        if ($trade->fixed_float === 'Fixed') return (float) $trade->fixed_price;
        return (float) ($trade->index?->latestPrice?->price ?? 0) + (float) $trade->spread;
    }
}
