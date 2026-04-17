<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\EobChecklist;
use App\Models\FinancialSettlement;
use App\Models\FinancialTrade;
use App\Models\GuidedScenario;
use App\Models\IndexDefinition;
use App\Models\IndexGridPoint;
use App\Models\Invoice;
use App\Models\Party;
use App\Models\Settlement;
use App\Models\Trade;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ── KPI: Pending validation ───────────────────────────────────────────
        $pendingPhysical  = Trade::where('trade_status', 'Pending')->count();
        $pendingFinancial = FinancialTrade::where('trade_status', 'Pending')->count();
        $pendingTotal     = $pendingPhysical + $pendingFinancial;

        // ── KPI: Portfolio MTM (active financial instruments) ─────────────────
        $activeFinancial = FinancialTrade::with([
            'floatIndex.latestPrice', 'secondIndex.latestPrice',
            'futuresIndex.latestPrice',
        ])->whereIn('trade_status', ['Active', 'Open'])->get();

        $swapMtm    = $activeFinancial->where('instrument_type', 'swap')
                          ->sum(fn($t) => $t->swapMtm());
        $futuresPnl = $activeFinancial->where('instrument_type', 'futures')
                          ->sum(fn($t) => $t->futuresUnrealisedPnl());
        $portfolioMtm = $swapMtm + $futuresPnl;

        // ── KPI: Physical unrealised P&L (float trades) ───────────────────────
        $physicalFloatTrades = Trade::with(['index.latestPrice'])
            ->whereIn('trade_status', ['Validated', 'Active'])
            ->where('fixed_float', 'Float')
            ->get();

        $physicalPnl = $physicalFloatTrades->sum(function ($t) {
            $market    = (float) ($t->index?->latestPrice?->price ?? 0);
            $bookPrice = (float) $t->fixed_price ?: ($market - (float) $t->spread);
            $effective = (float) $t->fixed_price ?? ($market + (float) $t->spread);
            $price     = $market + (float) $t->spread;
            $direction = $t->buy_sell === 'Buy' ? 1 : -1;
            return ($market - (float) $t->spread - $bookPrice) * (float) $t->quantity * $direction;
        });

        // ── KPI: Overdue settlements ───────────────────────────────────────────
        $physSettlOverdue = Settlement::where('settlement_status', 'Pending')
            ->where('payment_date', '<', today())->count();
        $finSettlOverdue  = FinancialSettlement::where('settlement_status', 'Pending')
            ->where('settlement_date', '<', today())->count();
        $settlementsOverdue = $physSettlOverdue + $finSettlOverdue;

        // ── KPI: Credit breaches ───────────────────────────────────────────────
        $creditBreachCount = $this->creditBreachCount();

        // ── KPI: EoB checklist today ──────────────────────────────────────────
        $eobToday    = EobChecklist::whereDate('checklist_date', today())->first();
        $eobComplete = $eobToday
            ? ((int) $eobToday->all_trades_validated
                + (int) $eobToday->all_invoices_issued
                + (int) $eobToday->all_settlements_confirmed
                + (int) $eobToday->all_nominations_matched)
            : null;

        // ── Actions: trades pending validation (most recent 6) ────────────────
        $pendingPhysicalTrades  = Trade::with(['counterparty', 'product'])
            ->where('trade_status', 'Pending')
            ->latest('trade_date')->limit(6)->get();
        $pendingFinancialTrades = FinancialTrade::with(['counterparty', 'product'])
            ->where('trade_status', 'Pending')
            ->latest('trade_date')->limit(6)->get();

        // ── Actions: overdue invoices ─────────────────────────────────────────
        $overdueInvoices = Invoice::with(['trade.counterparty'])
            ->whereIn('invoice_status', ['Draft', 'Issued'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', today())
            ->latest('due_date')->limit(5)->get();

        // ── Market prices with day-change ──────────────────────────────────────
        $marketPrices = $this->marketPrices();

        // ── Net position by product ────────────────────────────────────────────
        $netPositions = $this->netPositions();

        // ── Recent activity ────────────────────────────────────────────────────
        $recentActivity = AuditLog::with('user')
            ->latest()->limit(6)->get();

        // ── Training scenarios ────────────────────────────────────────────────
        $scenarios = GuidedScenario::where('is_active', true)
            ->orderBy('sort_order')->get();

        return view('dashboard', compact(
            'pendingTotal', 'pendingPhysical', 'pendingFinancial',
            'portfolioMtm', 'swapMtm', 'futuresPnl', 'physicalPnl',
            'settlementsOverdue', 'creditBreachCount',
            'eobToday', 'eobComplete',
            'pendingPhysicalTrades', 'pendingFinancialTrades',
            'overdueInvoices',
            'marketPrices', 'netPositions',
            'recentActivity', 'scenarios'
        ));
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function creditBreachCount(): int
    {
        $physExposure = Trade::whereIn('trade_status', ['Pending', 'Validated', 'Active'])
            ->with(['index.latestPrice'])
            ->get()
            ->groupBy('counterparty_id')
            ->map(fn($g) => $g->sum(fn($t) => $this->tradeValue($t)));

        return Party::where('internal_external', 'External')
            ->whereNotNull('credit_limit')
            ->where('credit_limit', '>', 0)
            ->get()
            ->filter(fn($p) => ($physExposure[$p->id] ?? 0) > (float) $p->credit_limit)
            ->count();
    }

    private function tradeValue(Trade $trade): float
    {
        if ($trade->fixed_float === 'Fixed') {
            return (float) $trade->quantity * (float) $trade->fixed_price;
        }
        $market = (float) ($trade->index?->latestPrice?->price ?? 0);
        return (float) $trade->quantity * ($market + (float) $trade->spread);
    }

    private function marketPrices(): array
    {
        return IndexDefinition::with(['latestPrice'])
            ->where('rec_status', 'Authorized')
            ->orderBy('index_name')
            ->get()
            ->map(function ($idx) {
                $latest = $idx->latestPrice;
                if (! $latest) return null;

                // Previous data point for day-change
                $prev = IndexGridPoint::where('index_id', $idx->id)
                    ->where('price_date', '<', $latest->price_date)
                    ->orderByDesc('price_date')
                    ->value('price');

                $change    = $prev ? (float) $latest->price - (float) $prev : null;
                $changePct = ($prev && $prev != 0) ? ($change / (float) $prev * 100) : null;

                return [
                    'name'       => $idx->index_name,
                    'price'      => (float) $latest->price,
                    'change'     => $change,
                    'change_pct' => $changePct,
                    'uom'        => $idx->uom?->code ?? '',
                ];
            })
            ->filter()
            ->values()
            ->toArray();
    }

    private function netPositions(): array
    {
        return Trade::with('product')
            ->whereIn('trade_status', ['Validated', 'Active'])
            ->get()
            ->groupBy('product_id')
            ->map(function ($group) {
                $product = $group->first()->product;
                $long  = $group->where('buy_sell', 'Buy')->sum('quantity');
                $short = $group->where('buy_sell', 'Sell')->sum('quantity');
                $net   = $long - $short;
                return [
                    'product' => $product?->name ?? 'Unknown',
                    'long'    => (float) $long,
                    'short'   => (float) $short,
                    'net'     => (float) $net,
                ];
            })
            ->sortByDesc(fn($r) => abs($r['net']))
            ->values()
            ->toArray();
    }
}
