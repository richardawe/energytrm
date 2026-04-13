<?php

namespace App\Http\Controllers\Trades;

use App\Http\Controllers\Controller;
use App\Models\Agreement;
use App\Models\Broker;
use App\Models\Currency;
use App\Models\IndexDefinition;
use App\Models\Incoterm;
use App\Models\Party;
use App\Models\PaymentTerm;
use App\Models\Portfolio;
use App\Models\Product;
use App\Models\Trade;
use App\Models\Uom;
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

        $trades        = $query->paginate(25)->withQueryString();
        $products      = Product::orderBy('name')->get();
        $counterparties = Party::scopeExternal(Party::query())->scopeAuthorized(Party::query())->orderBy('short_name')->get();

        return view('trades.index', compact('trades', 'products', 'counterparties'));
    }

    public function create()
    {
        return view('trades.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validateTrade($request);

        DB::transaction(function () use ($data, $request) {
            $data['deal_number']        = Trade::nextDealNumber();
            $data['transaction_number'] = Trade::nextTransactionNumber();
            $data['instrument_number']  = Trade::nextInstrumentNumber();
            $data['pay_rec']            = Trade::derivePayRec($data['buy_sell']);
            $data['trade_status']       = 'Pending';
            $data['version']            = 1;
            $data['created_by']         = auth()->id();

            Trade::create($data);
        });

        return redirect()->route('trades.index')->with('success', 'Trade captured successfully.');
    }

    public function show(Trade $trade)
    {
        $trade->load([
            'internalBu', 'portfolio', 'counterparty', 'product', 'uom',
            'index', 'currency', 'paymentTerms', 'broker', 'agreement',
            'createdBy', 'validatedBy',
        ]);
        return view('trades.show', compact('trade'));
    }

    public function edit(Trade $trade)
    {
        if ($trade->trade_status === 'Settled') {
            return redirect()->route('trades.show', $trade)
                ->with('error', 'Settled trades cannot be amended.');
        }

        return view('trades.edit', array_merge(['trade' => $trade], $this->formData()));
    }

    public function update(Request $request, Trade $trade)
    {
        if ($trade->trade_status === 'Settled') {
            return redirect()->route('trades.show', $trade)
                ->with('error', 'Settled trades cannot be amended.');
        }

        $data = $this->validateTrade($request, $trade);

        DB::transaction(function () use ($data, $trade) {
            $data['transaction_number'] = Trade::nextTransactionNumber();
            $data['pay_rec']            = Trade::derivePayRec($data['buy_sell']);
            $data['version']            = $trade->version + 1;
            // Reset to Pending on amendment if it was Validated
            if ($trade->trade_status === 'Validated') {
                $data['trade_status']  = 'Pending';
                $data['validated_by']  = null;
                $data['validated_at']  = null;
            }

            $trade->update($data);
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

        return redirect()->route('trades.show', $trade)
            ->with('success', 'Trade validated.');
    }

    public function revert(Trade $trade)
    {
        if ($trade->trade_status !== 'Validated') {
            return back()->with('error', 'Only Validated trades can be reverted to Pending.');
        }

        $trade->update([
            'trade_status' => 'Pending',
            'validated_by' => null,
            'validated_at' => null,
        ]);

        return redirect()->route('trades.show', $trade)
            ->with('success', 'Trade reverted to Pending.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function formData(): array
    {
        return [
            'internalBus'    => Party::where('internal_external', 'Internal')
                                     ->where('party_type', 'BU')
                                     ->where('status', 'Authorized')
                                     ->orderBy('short_name')->get(),
            'portfolios'     => Portfolio::orderBy('name')->get(),
            'counterparties' => Party::where('internal_external', 'External')
                                     ->where('status', 'Authorized')
                                     ->orderBy('short_name')->get(),
            'products'       => Product::where('status', 'Authorized')->orderBy('name')->get(),
            'uoms'           => Uom::orderBy('code')->get(),
            'indices'        => IndexDefinition::where('status', 'Authorized')->orderBy('index_name')->get(),
            'currencies'     => Currency::where('is_active', true)->orderBy('code')->get(),
            'paymentTerms'   => PaymentTerm::orderBy('name')->get(),
            'incoterms'      => Incoterm::orderBy('code')->get(),
            'brokers'        => Broker::where('status', 'Authorized')->orderBy('name')->get(),
            'agreements'     => Agreement::where('status', 'Authorized')->orderBy('name')->get(),
        ];
    }

    private function validateTrade(Request $request, ?Trade $trade = null): array
    {
        return $request->validate([
            'trade_date'        => 'required|date',
            'buy_sell'          => 'required|in:Buy,Sell',
            'start_date'        => 'required|date',
            'end_date'          => 'required|date|after_or_equal:start_date',
            'internal_bu_id'    => 'required|exists:parties,id',
            'portfolio_id'      => 'required|exists:portfolios,id',
            'counterparty_id'   => 'required|exists:parties,id',
            'product_id'        => 'required|exists:products,id',
            'quantity'          => 'required|numeric|min:0.0001',
            'volume_type'       => 'required|in:Fixed,Variable,Optional',
            'uom_id'            => 'required|exists:uoms,id',
            'fixed_float'       => 'required|in:Fixed,Float',
            'index_id'          => 'required_if:fixed_float,Float|nullable|exists:index_definitions,id',
            'fixed_price'       => 'required_if:fixed_float,Fixed|nullable|numeric|min:0',
            'spread'            => 'nullable|numeric',
            'currency_id'       => 'required|exists:currencies,id',
            'payment_terms_id'  => 'nullable|exists:payment_terms,id',
            'incoterm_code'     => 'nullable|string|max:10',
            'load_port'         => 'nullable|string|max:100',
            'discharge_port'    => 'nullable|string|max:100',
            'broker_id'         => 'nullable|exists:brokers,id',
            'agreement_id'      => 'nullable|exists:agreements,id',
            'comments'          => 'nullable|string|max:1000',
        ]);
    }
}
