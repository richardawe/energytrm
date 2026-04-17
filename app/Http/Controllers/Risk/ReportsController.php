<?php

namespace App\Http\Controllers\Risk;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Trade;
use App\Models\Party;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReportsController extends Controller
{
    private const TYPES = [
        'portfolio_analysis'    => 'Portfolio Analysis',
        'pnl'                   => 'P&L Summary',
        'counterparty_exposure' => 'Counterparty Exposure',
    ];

    public function index()
    {
        $reports = Report::with('generatedBy')
            ->orderByDesc('created_at')
            ->paginate(20);

        $types = self::TYPES;

        return view('risk.reports', compact('reports', 'types'));
    }

    public function generate(Request $request): Response
    {
        $data = $request->validate([
            'report_type'    => ['required', 'in:' . implode(',', array_keys(self::TYPES))],
            'reporting_date' => ['required', 'date'],
        ]);

        $csv = match ($data['report_type']) {
            'portfolio_analysis'    => $this->portfolioAnalysisCsv(),
            'pnl'                   => $this->pnlCsv(),
            'counterparty_exposure' => $this->counterpartyExposureCsv(),
        };

        // Log the report run
        Report::create([
            'report_type'    => $data['report_type'],
            'reporting_date' => $data['reporting_date'],
            'parameters'     => json_encode(['as_of' => $data['reporting_date']]),
            'file_format'    => 'csv',
            'generated_by'   => auth()->id(),
        ]);

        $filename = $data['report_type'] . '_' . $data['reporting_date'] . '.csv';

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // ── CSV builders ──────────────────────────────────────────────────────────

    private function portfolioAnalysisCsv(): string
    {
        $trades = Trade::with(['product', 'uom', 'currency', 'portfolio', 'index.latestPrice'])
            ->whereIn('trade_status', ['Pending', 'Validated', 'Active', 'Settled'])
            ->get();

        $rows   = [['Portfolio', 'Product', 'UOM', 'Net Qty', 'Trade Value', 'MTM Value', 'Unrealised PnL', 'Currency']];

        foreach ($trades->groupBy('portfolio_id') as $pGroup) {
            $portfolio = $pGroup->first()->portfolio?->name ?? 'Unknown';
            foreach ($pGroup->groupBy('product_id') as $group) {
                $p       = $group->first()->product;
                $uom     = $group->first()->uom;
                $ccy     = $group->first()->currency;
                $netQty  = $group->sum(fn($t) => $t->buy_sell === 'Buy'
                    ? (float) $t->quantity : -(float) $t->quantity);
                $tv      = $group->sum(fn($t) => $this->tv($t));
                $mtm     = $group->sum(fn($t) => $this->mtm($t));
                $upnl    = $group->sum(fn($t) => $this->upnl($t));
                $rows[]  = [$portfolio, $p?->name, $uom?->code, $netQty, round($tv, 2), round($mtm, 2), round($upnl, 2), $ccy?->code];
            }
        }

        return $this->toCsv($rows);
    }

    private function pnlCsv(): string
    {
        $trades = Trade::with(['product', 'uom', 'currency', 'counterparty', 'index.latestPrice', 'invoices.settlements'])
            ->whereIn('trade_status', ['Validated', 'Active', 'Settled'])
            ->orderByDesc('trade_date')
            ->get();

        $rows = [['Deal No', 'Trade Date', 'Counterparty', 'Product', 'B/S', 'Qty', 'UOM', 'Trade Price', 'Market Price', 'Trade Value', 'Unrealised PnL', 'Realised PnL', 'Status', 'Currency']];

        foreach ($trades as $t) {
            $tp  = $this->tradePrice($t);
            $mp  = (float) ($t->index?->latestPrice?->price ?? $tp);
            $tv  = (float) $t->quantity * $tp;
            $dir = $t->buy_sell === 'Buy' ? 1 : -1;
            $up  = $t->fixed_float === 'Float' ? ($mp - $tp) * (float) $t->quantity * $dir : null;

            $invoiceTotal  = (float) $t->invoices->sum('invoice_amount');
            $settledTotal  = $t->invoices->flatMap->settlements->where('settlement_status', 'Confirmed')->sum('payment_amount');
            $rp            = $t->trade_status === 'Settled'
                ? ($t->buy_sell === 'Sell' ? $settledTotal - $invoiceTotal : $invoiceTotal - $settledTotal)
                : null;

            $rows[] = [
                $t->deal_number, $t->trade_date->format('Y-m-d'),
                $t->counterparty?->short_name, $t->product?->name,
                $t->buy_sell, (float) $t->quantity, $t->uom?->code,
                round($tp, 4), $t->fixed_float === 'Float' ? round($mp, 4) : '',
                round($tv, 2), $up !== null ? round($up, 2) : '',
                $rp !== null ? round($rp, 2) : '',
                $t->trade_status, $t->currency?->code,
            ];
        }

        return $this->toCsv($rows);
    }

    private function counterpartyExposureCsv(): string
    {
        $trades = Trade::with(['counterparty.creditLimitCurrency', 'index.latestPrice'])
            ->whereIn('trade_status', ['Pending', 'Validated', 'Active'])
            ->get();

        $rows = [['Counterparty', 'Trade Count', 'Total Exposure', 'Credit Limit', 'Utilisation %', 'Breached', 'CCY']];

        foreach ($trades->groupBy('counterparty_id')->sortByDesc(fn($g) => $g->sum(fn($t) => $this->tv($t))) as $group) {
            $party     = $group->first()->counterparty;
            $exp       = $group->sum(fn($t) => $this->tv($t));
            $limit     = (float) ($party?->credit_limit ?? 0);
            $util      = $limit > 0 ? round($exp / $limit * 100, 1) : '';
            $breached  = $limit > 0 && $exp > $limit ? 'YES' : 'NO';
            $rows[]    = [$party?->short_name, $group->count(), round($exp, 2), $limit ?: '', $util, $breached, $party?->creditLimitCurrency?->code ?? ''];
        }

        return $this->toCsv($rows);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function tradePrice(Trade $trade): float
    {
        if ($trade->fixed_float === 'Fixed') return (float) $trade->fixed_price;
        return (float) ($trade->index?->latestPrice?->price ?? 0) + (float) $trade->spread;
    }

    private function tv(Trade $t): float
    {
        return (float) $t->quantity * $this->tradePrice($t);
    }

    private function mtm(Trade $t): float
    {
        $mp = (float) ($t->index?->latestPrice?->price ?? $this->tradePrice($t));
        return (float) $t->quantity * $mp;
    }

    private function upnl(Trade $t): float
    {
        if ($t->fixed_float !== 'Float') return 0.0;
        $mp  = (float) ($t->index?->latestPrice?->price ?? 0);
        $tp  = $this->tradePrice($t);
        $dir = $t->buy_sell === 'Buy' ? 1 : -1;
        return ($mp - $tp) * (float) $t->quantity * $dir;
    }

    private function toCsv(array $rows): string
    {
        $handle = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);
        return $csv;
    }
}
