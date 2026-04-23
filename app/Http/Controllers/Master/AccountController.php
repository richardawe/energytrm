<?php
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Currency;
use App\Models\Party;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::with(['holdingParty', 'currency'])
            ->orderBy('account_number')
            ->paginate(25);
        return view('master.accounts.index', compact('accounts'));
    }

    public function create()
    {
        $holdingParties = Party::orderBy('short_name')->get();
        $currencies     = Currency::orderBy('code')->get();
        return view('master.accounts.create', compact('holdingParties', 'currencies'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'account_number'        => 'required|string|max:50|unique:accounts,account_number',
            'account_name'          => 'required|string|max:150',
            'account_type'          => 'required|in:Nostro,Internal Nostro,Vostro,Internal Vostro,Margin,Other',
            'holding_party_id'      => 'nullable|exists:parties,id',
            'currency_id'           => 'nullable|exists:currencies,id',
            'status'                => 'required|in:Authorized,Auth Pending,Do Not Use,Amendment Pending',
            'class'                 => 'nullable|string|max:100',
            'description'           => 'nullable|string',
            'on_balance_sheet'      => 'boolean',
            'allow_multiple_units'  => 'boolean',
            'account_legal_name'    => 'nullable|string|max:200',
            'country'               => 'nullable|string|max:100',
            'date_opened'           => 'nullable|date',
            'date_closed'           => 'nullable|date|after_or_equal:date_opened',
            'general_ledger_account'=> 'nullable|string|max:100',
            'sweep_enabled'         => 'boolean',
        ]);

        $data['on_balance_sheet']     = $request->boolean('on_balance_sheet');
        $data['allow_multiple_units'] = $request->boolean('allow_multiple_units');
        $data['sweep_enabled']        = $request->boolean('sweep_enabled');
        $data['created_by']           = Auth::id();
        $data['version']              = 0;

        $account = Account::create($data);

        return redirect()->route('master.accounts.show', $account)->with('success', 'Account created.');
    }

    public function show(Account $account)
    {
        $account->load(['holdingParty', 'currency', 'author']);
        return view('master.accounts.show', compact('account'));
    }

    public function edit(Account $account)
    {
        $holdingParties = Party::orderBy('short_name')->get();
        $currencies     = Currency::orderBy('code')->get();
        return view('master.accounts.edit', compact('account', 'holdingParties', 'currencies'));
    }

    public function update(Request $request, Account $account)
    {
        $data = $request->validate([
            'account_number'        => 'required|string|max:50|unique:accounts,account_number,'.$account->id,
            'account_name'          => 'required|string|max:150',
            'account_type'          => 'required|in:Nostro,Internal Nostro,Vostro,Internal Vostro,Margin,Other',
            'holding_party_id'      => 'nullable|exists:parties,id',
            'currency_id'           => 'nullable|exists:currencies,id',
            'status'                => 'required|in:Authorized,Auth Pending,Do Not Use,Amendment Pending',
            'class'                 => 'nullable|string|max:100',
            'description'           => 'nullable|string',
            'on_balance_sheet'      => 'boolean',
            'allow_multiple_units'  => 'boolean',
            'account_legal_name'    => 'nullable|string|max:200',
            'country'               => 'nullable|string|max:100',
            'date_opened'           => 'nullable|date',
            'date_closed'           => 'nullable|date|after_or_equal:date_opened',
            'general_ledger_account'=> 'nullable|string|max:100',
            'sweep_enabled'         => 'boolean',
        ]);

        $data['on_balance_sheet']     = $request->boolean('on_balance_sheet');
        $data['allow_multiple_units'] = $request->boolean('allow_multiple_units');
        $data['sweep_enabled']        = $request->boolean('sweep_enabled');
        $data['version']              = $account->version + 1;

        $account->update($data);

        return redirect()->route('master.accounts.show', $account)->with('success', 'Account updated (v'.$data['version'].').');
    }

    public function destroy(Account $account)
    {
        $account->delete();
        return redirect()->route('master.accounts.index')->with('success', 'Account deleted.');
    }
}
