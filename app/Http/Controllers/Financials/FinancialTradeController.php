<?php

namespace App\Http\Controllers\Financials;

use App\Http\Controllers\Controller;
use App\Models\Agreement;
use App\Models\AuditLog;
use App\Models\Broker;
use App\Models\Currency;
use App\Models\FinancialTrade;
use App\Models\IndexDefinition;
use App\Models\Party;
use App\Models\Portfolio;
use App\Models\Product;
use App\Models\Uom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancialTradeController extends Controller
{
    public function index(Request $request)
    {
        $query = FinancialTrade::with([
            'counterparty', 'product', 'currency', 'internalBu', 'portfolio',
        ])->latest('trade_date')->latest('id');

        if ($request->filled('instrument_type')) {
            $query->where('instrument_type', $request->instrument_type);
        }
        if ($request->filled('status')) {
            $query->where('trade_status', $request->status);
        }
        if ($request->filled('buy_sell')) {
            $query->where('buy_sell', $request->buy_sell);
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
        $counterparties = Party::where('internal_external', 'External')
                               ->where('status', 'Authorized')
                               ->orderBy('short_name')->get();

        return view('financials.financial-trades.index', compact('trades', 'counterparties'));
    }

    public function create(Request $request)
    {
        $instrumentType = $request->get('type', 'swap');
        return view('financials.financial-trades.create', array_merge(
            ['instrumentType' => $instrumentType],
            $this->formData()
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validateTrade($request);

        $trade = null;
        DB::transaction(function () use ($data, &$trade) {
            $data['deal_number']        = FinancialTrade::nextDealNumber();
            $data['transaction_number'] = FinancialTrade::nextTransactionNumber();
            $data['instrument_number']  = FinancialTrade::nextInstrumentNumber();
            $data['pay_rec']            = FinancialTrade::derivePayRec($data['buy_sell']);
            $data['trade_status']       = 'Pending';
            $data['version']            = 1;
            $data['created_by']         = auth()->id();

            $trade = FinancialTrade::create($data);
            AuditLog::record($trade, 'created', [], $trade->getAttributes());
        });

        return redirect()->route('financials.financial-trades.show', $trade)
            ->with('success', 'Financial trade captured successfully.');
    }

    public function show(FinancialTrade $financialTrade)
    {
        $financialTrade->load([
            'internalBu', 'portfolio', 'counterparty', 'product', 'uom',
            'currency', 'broker', 'agreement',
            'floatIndex', 'secondIndex', 'futuresIndex', 'underlyingIndex',
            'createdBy', 'validatedBy',
            'hedgesPhysicalTrade.product', 'hedgesPhysicalTrade.currency', 'hedgesPhysicalTrade.uom',
            'settlements.createdBy',
            'auditLogs.user',
        ]);
        return view('financials.financial-trades.show', ['trade' => $financialTrade]);
    }

    public function edit(FinancialTrade $financialTrade)
    {
        if (in_array($financialTrade->trade_status, FinancialTrade::TERMINAL_STATUSES)) {
            return redirect()->route('financials.financial-trades.show', $financialTrade)
                ->with('error', 'This trade cannot be amended.');
        }

        return view('financials.financial-trades.edit', array_merge(
            ['trade' => $financialTrade],
            $this->formData()
        ));
    }

    public function update(Request $request, FinancialTrade $financialTrade)
    {
        if (in_array($financialTrade->trade_status, FinancialTrade::TERMINAL_STATUSES)) {
            return redirect()->route('financials.financial-trades.show', $financialTrade)
                ->with('error', 'This trade cannot be amended.');
        }

        $data = $this->validateTrade($request, $financialTrade);

        DB::transaction(function () use ($data, $financialTrade) {
            $old = $financialTrade->getAttributes();

            $data['transaction_number'] = FinancialTrade::nextTransactionNumber();
            $data['pay_rec']            = FinancialTrade::derivePayRec($data['buy_sell']);
            $data['version']            = $financialTrade->version + 1;

            if ($financialTrade->trade_status === 'Validated') {
                $data['trade_status']  = 'Pending';
                $data['validated_by']  = null;
                $data['validated_at']  = null;
            }

            $financialTrade->update($data);
            AuditLog::record($financialTrade, 'updated', $old, $financialTrade->fresh()->getAttributes());
        });

        return redirect()->route('financials.financial-trades.show', $financialTrade)
            ->with('success', "Trade amended — now v{$financialTrade->fresh()->version} (new TXN issued).");
    }

    public function validate(FinancialTrade $financialTrade)
    {
        if ($financialTrade->trade_status !== 'Pending') {
            return back()->with('error', 'Only Pending trades can be validated.');
        }

        $validatedStatus = FinancialTrade::VALIDATED_STATUS[$financialTrade->instrument_type];

        $financialTrade->update([
            'trade_status' => $validatedStatus,
            'validated_by' => auth()->id(),
            'validated_at' => now(),
        ]);
        AuditLog::record($financialTrade, 'validated');

        return redirect()->route('financials.financial-trades.show', $financialTrade)
            ->with('success', "Trade validated — status set to {$validatedStatus}.");
    }

    public function revert(FinancialTrade $financialTrade)
    {
        if ($financialTrade->trade_status === 'Pending') {
            return back()->with('error', 'Trade is already Pending.');
        }
        if (in_array($financialTrade->trade_status, FinancialTrade::TERMINAL_STATUSES)) {
            return back()->with('error', 'Terminal trades cannot be reverted.');
        }

        $financialTrade->update([
            'trade_status' => 'Pending',
            'validated_by' => null,
            'validated_at' => null,
        ]);
        AuditLog::record($financialTrade, 'reverted');

        return redirect()->route('financials.financial-trades.show', $financialTrade)
            ->with('success', 'Trade reverted to Pending.');
    }

    public function destroy(FinancialTrade $financialTrade)
    {
        if ($financialTrade->trade_status !== 'Pending') {
            return back()->with('error', 'Only Pending trades can be deleted.');
        }
        $financialTrade->delete();
        return redirect()->route('financials.financial-trades.index')
            ->with('success', 'Trade deleted.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function formData(): array
    {
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
            'brokers'         => Broker::where('status', 'Active')->orderBy('name')->get(),
            'agreements'      => Agreement::where('status', 'Authorized')->orderBy('name')->get(),
            'clearingParties' => Party::where('internal_external', 'External')
                                      ->where('status', 'Authorized')
                                      ->orderBy('short_name')->get(),
        ];
    }

    private function validateTrade(Request $request, ?FinancialTrade $trade = null): array
    {
        $type = $request->input('instrument_type', $trade?->instrument_type ?? 'swap');

        $common = [
            'instrument_type'  => 'required|in:swap,futures,options',
            'trade_date'       => 'required|date',
            'buy_sell'         => 'required|in:Buy,Sell',
            'internal_bu_id'   => 'required|exists:parties,id',
            'portfolio_id'     => 'required|exists:portfolios,id',
            'counterparty_id'  => 'required|exists:parties,id',
            'currency_id'      => 'required|exists:currencies,id',
            'product_id'       => 'required|exists:products,id',
            'broker_id'        => 'nullable|exists:brokers,id',
            'agreement_id'     => 'nullable|exists:agreements,id',
            'comments'         => 'nullable|string|max:1000',
        ];

        $typeRules = match ($type) {
            'swap' => [
                'swap_type'          => 'required|in:commodity,basis',
                'fixed_rate'         => 'required|numeric|min:0',
                'float_index_id'     => 'required|exists:index_definitions,id',
                'second_index_id'    => 'required_if:swap_type,basis|nullable|exists:index_definitions,id',
                'notional_quantity'  => 'required|numeric|min:0.0001',
                'uom_id'             => 'required|exists:uoms,id',
                'spread'             => 'nullable|numeric',
                'payment_frequency'  => 'required|in:Monthly,Quarterly',
                'start_date'         => 'required|date',
                'end_date'           => 'required|date|after_or_equal:start_date',
            ],
            'futures' => [
                'exchange'           => 'required|string|max:50',
                'contract_code'      => 'required|string|max:30',
                'expiry_date'        => 'required|date',
                'num_contracts'      => 'required|integer|min:1',
                'contract_size'      => 'required|numeric|min:0.0001',
                'futures_price'      => 'required|numeric|min:0',
                'margin_requirement' => 'nullable|numeric|min:0',
                'futures_index_id'   => 'nullable|exists:index_definitions,id',
            ],
            'options' => [
                'option_type'        => 'required|in:call,put',
                'exercise_style'     => 'required|in:European,American',
                'strike_price'       => 'required|numeric|min:0',
                'option_expiry_date' => 'required|date',
                'premium'            => 'required|numeric|min:0',
                'underlying_index_id'=> 'nullable|exists:index_definitions,id',
                'volatility'         => 'nullable|numeric|min:0|max:9.999999',
            ],
            default => [],
        };

        return $request->validate(array_merge($common, $typeRules));
    }
}
