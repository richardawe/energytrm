<?php

namespace App\Http\Controllers\Trades;

use App\Http\Controllers\Controller;
use App\Models\Agreement;
use App\Models\Broker;
use App\Models\Currency;
use App\Models\FinancialTrade;
use App\Models\IndexDefinition;
use App\Models\Incoterm;
use App\Models\Party;
use App\Models\PaymentTerm;
use App\Models\Pipeline;
use App\Models\Portfolio;
use App\Models\Product;
use App\Models\Trade;
use App\Models\AuditLog;
use App\Models\Uom;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TradeController extends Controller
{
    public function index(Request $request)
    {
        $query = Trade::with([
            'counterparty', 'product', 'uom', 'currency', 'internalBu', 'portfolio',
        ])->latest('trade_date')->latest('id');

        if ($request->filled('status')) {
            $query->where('trade_status', $request->status);
        }
        if ($request->filled('buy_sell')) {
            $query->where('buy_sell', $request->buy_sell);
        }
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('counterparty_id')) {
            $query->where('counterparty_id', $request->counterparty_id);
        }
        if ($request->filled('date_from')) {
            $query->where('trade_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('trade_date', '<=', $request->date_to);
        }

        $trades         = $query->paginate(25)->withQueryString();
        $products       = Product::orderBy('name')->get();
        $counterparties = Party::where('internal_external', 'External')
                                ->where('status', 'Authorized')
                                ->orderBy('short_name')->get();

        return view('trades.index', compact('trades', 'products', 'counterparties'));
    }

    public function create()
    {
        return view('trades.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validateTrade($request);

        $trade = null;
        DB::transaction(function () use ($data, &$trade) {
            $data['deal_number']        = Trade::nextDealNumber();
            $data['transaction_number'] = Trade::nextTransactionNumber();
            $data['instrument_number']  = Trade::nextInstrumentNumber();
            $data['pay_rec']            = Trade::derivePayRec($data['buy_sell']);
            $data['trade_status']       = 'Pending';
            $data['version']            = 1;
            $data['created_by']         = auth()->id();
            // Default trader to logged-in user if not explicitly chosen
            $data['trader_id'] = $data['trader_id'] ?? auth()->id();

            $trade = Trade::create($data);
            AuditLog::record($trade, 'created', [], $trade->getAttributes());
        });

        // Credit limit breach check — warn but do not block
        $warning = $this->creditLimitWarning($data['counterparty_id']);

        return redirect()->route('trades.index')
            ->with('success', 'Trade captured successfully.')
            ->with('warning', $warning);
    }

    public function show(Trade $trade)
    {
        $trade->load([
            'internalBu', 'portfolio', 'counterparty', 'product', 'uom', 'priceUnit',
            'index', 'currency', 'paymentTerms', 'broker', 'agreement',
            'pipeline', 'zone', 'location',
            'trader', 'createdBy', 'validatedBy',
            'hedgedBy.product', 'hedgedBy.currency',
            'auditLogs.user',
        ]);
        return view('trades.show', compact('trade'));
    }

    public function edit(Trade $trade)
    {
        if (in_array($trade->trade_status, ['Settled', 'Closed'])) {
            return redirect()->route('trades.show', $trade)
                ->with('error', 'This trade cannot be amended.');
        }

        return view('trades.edit', array_merge(['trade' => $trade], $this->formData()));
    }

    public function update(Request $request, Trade $trade)
    {
        if (in_array($trade->trade_status, ['Settled', 'Closed'])) {
            return redirect()->route('trades.show', $trade)
                ->with('error', 'This trade cannot be amended.');
        }

        $data = $this->validateTrade($request, $trade);

        DB::transaction(function () use ($data, $trade) {
            $old = $trade->getAttributes();

            $data['transaction_number'] = Trade::nextTransactionNumber();
            $data['pay_rec']            = Trade::derivePayRec($data['buy_sell']);
            $data['version']            = $trade->version + 1;
            // Reset to Pending on amendment if it was Validated or Active
            if (in_array($trade->trade_status, ['Validated', 'Active'])) {
                $data['trade_status']  = 'Pending';
                $data['validated_by']  = null;
                $data['validated_at']  = null;
            }

            $trade->update($data);
            AuditLog::record($trade, 'updated', $old, $trade->fresh()->getAttributes());
        });

        return redirect()->route('trades.show', $trade)
            ->with('success', "Trade amended — now v{$trade->fresh()->version} (new TXN issued).");
    }

    public function validate(Trade $trade)
    {
        if ($trade->trade_status !== 'Pending') {
            return back()->with('error', 'Only Pending trades can be validated.');
        }

        $trade->update([
            'trade_status' => 'Validated',
            'validated_by' => auth()->id(),
            'validated_at' => now(),
        ]);
        AuditLog::record($trade, 'validated');

        return redirect()->route('trades.show', $trade)
            ->with('success', 'Trade validated.');
    }

    public function revert(Trade $trade)
    {
        if (!in_array($trade->trade_status, ['Validated', 'Active'])) {
            return back()->with('error', 'Only Validated or Active trades can be reverted to Pending.');
        }

        $trade->update([
            'trade_status' => 'Pending',
            'validated_by' => null,
            'validated_at' => null,
        ]);
        AuditLog::record($trade, 'reverted');

        return redirect()->route('trades.show', $trade)
            ->with('success', 'Trade reverted to Pending.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function creditLimitWarning(int $counterpartyId): ?string
    {
        $party = Party::find($counterpartyId);
        if (!$party || !$party->credit_limit) return null;

        $exposure = Trade::where('counterparty_id', $counterpartyId)
            ->whereIn('trade_status', ['Pending', 'Validated', 'Active'])
            ->get()
            ->sum(function (Trade $t) {
                $price = $t->fixed_float === 'Fixed'
                    ? (float) $t->fixed_price
                    : (float) ($t->index?->latestPrice?->price ?? 0) + (float) $t->spread;
                return (float) $t->quantity * $price;
            });

        if ($exposure > (float) $party->credit_limit) {
            return "Credit limit breach: {$party->short_name} exposure "
                . number_format($exposure, 2)
                . ' exceeds limit of '
                . number_format((float) $party->credit_limit, 2)
                . ". See <a href=\"" . route('risk.counterparty-exposure') . "\">Counterparty Exposure</a>.";
        }

        return null;
    }

    private function formData(): array
    {
        $pipelines = Pipeline::with('zones.locations')->authorized()->orderBy('code')->get();

        // Build cascade maps for Alpine.js: { pipeline_id: [{id, zone_code, zone_name, locations:[...]}] }
        $pipelineCascade = $pipelines->mapWithKeys(fn ($p) => [
            $p->id => $p->zones->map(fn ($z) => [
                'id'        => $z->id,
                'zone_code' => $z->zone_code,
                'zone_name' => $z->zone_name,
                'locations' => $z->locations->map(fn ($l) => [
                    'id'            => $l->id,
                    'location_code' => $l->location_code,
                    'location_name' => $l->location_name,
                    'location_type' => $l->location_type,
                ])->values(),
            ])->values(),
        ]);

        // Product → index filter map: { product_id: [index_id, ...] }
        $productIndexMap = IndexDefinition::where('rec_status', 'Authorized')
            ->whereNotNull('product_id')
            ->pluck('product_id', 'id')
            ->groupBy(fn ($pid) => $pid)
            ->map(fn ($group) => $group->keys()->values());

        return [
            'internalBus'     => Party::where('internal_external', 'Internal')
                                      ->where('party_type', 'BU')
                                      ->where('status', 'Authorized')
                                      ->orderBy('short_name')->get(),
            'portfolios'      => Portfolio::orderBy('name')->get(),
            'counterparties'  => Party::where('internal_external', 'External')
                                      ->where('status', 'Authorized')
                                      ->orderBy('short_name')->get(),
            'products'        => Product::where('status', 'Authorized')->orderBy('name')->get(),
            'uoms'            => Uom::orderBy('code')->get(),
            'indices'         => IndexDefinition::where('rec_status', 'Authorized')->orderBy('index_name')->get(),
            'currencies'      => Currency::where('is_active', true)->orderBy('code')->get(),
            'paymentTerms'    => PaymentTerm::orderBy('name')->get(),
            'incoterms'       => Incoterm::orderBy('code')->get(),
            'brokers'         => Broker::where('status', 'Authorized')->orderBy('name')->get(),
            'agreements'      => Agreement::where('status', 'Authorized')->orderBy('name')->get(),
            'traders'         => User::orderBy('name')->get(),
            'pipelines'       => $pipelines,
            'pipelineCascade' => $pipelineCascade,
            'productIndexMap' => $productIndexMap,
            'financialTrades' => FinancialTrade::whereIn('trade_status', ['Active', 'Open', 'Validated'])
                                               ->orderBy('deal_number')->get(),
        ];
    }

    private function validateTrade(Request $request, ?Trade $trade = null): array
    {
        return $request->validate([
            'trade_date'                    => 'required|date',
            'buy_sell'                      => 'required|in:Buy,Sell',
            'start_date'                    => 'required|date',
            'end_date'                      => 'required|date|after_or_equal:start_date',
            'internal_bu_id'                => 'required|exists:parties,id',
            'portfolio_id'                  => 'required|exists:portfolios,id',
            'counterparty_id'               => 'required|exists:parties,id',
            'trader_id'                     => 'nullable|exists:users,id',
            'product_id'                    => 'required|exists:products,id',
            'quantity'                      => 'required|numeric|min:0.0001',
            'volume_type'                   => 'required|in:Fixed,Variable,Optional',
            'uom_id'                        => 'required|exists:uoms,id',
            'price_unit_id'                 => 'nullable|exists:uoms,id',
            'fixed_float'                   => 'required|in:Fixed,Float',
            'index_id'                      => 'required_if:fixed_float,Float|nullable|exists:index_definitions,id',
            'fixed_price'                   => 'required_if:fixed_float,Fixed|nullable|numeric|min:0',
            'spread'                        => 'nullable|numeric',
            'reference_source'              => 'nullable|string|max:50',
            'put_call'                      => 'nullable|in:Put,Call',
            'currency_id'                   => 'required|exists:currencies,id',
            'payment_terms_id'              => 'nullable|exists:payment_terms,id',
            'incoterm_code'                 => 'nullable|string|max:10',
            'load_port'                     => 'nullable|string|max:100',
            'discharge_port'                => 'nullable|string|max:100',
            'pipeline_id'                   => 'nullable|exists:pipelines,id',
            'zone_id'                       => 'nullable|exists:pipeline_zones,id',
            'location_id'                   => 'nullable|exists:pipeline_locations,id',
            'fuel_percent'                  => 'nullable|numeric|min:0|max:100',
            'broker_id'                     => 'nullable|exists:brokers,id',
            'agreement_id'                  => 'nullable|exists:agreements,id',
            'comments'                      => 'nullable|string|max:1000',
            'hedged_by_financial_trade_id'  => 'nullable|exists:financial_trades,id',
        ]);
    }
}
