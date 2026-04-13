<?php
namespace App\Http\Controllers\Master;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Party;
use Illuminate\Http\Request;

class PartyController extends Controller
{
    public function index(Request $request)
    {
        $query = Party::with('parent')->orderBy('party_type')->orderBy('short_name');
        if ($request->type)   $query->where('party_type', $request->type);
        if ($request->ie)     $query->where('internal_external', $request->ie);
        if ($request->status) $query->where('status', $request->status);
        return view('master.parties.index', ['parties' => $query->paginate(25)->withQueryString()]);
    }

    public function create()
    {
        return view('master.parties.create', [
            'parents'    => Party::whereIn('party_type', ['Group', 'LE'])->authorized()->orderBy('short_name')->get(),
            'currencies' => Currency::orderBy('code')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'party_type'              => 'required|in:Group,LE,BU',
            'internal_external'       => 'required|in:Internal,External',
            'parent_id'               => 'nullable|exists:parties,id',
            'short_name'              => 'required|string|max:32|unique:parties',
            'long_name'               => 'required|string|max:255',
            'status'                  => 'required|in:Auth Pending,Authorized,Do Not Use',
            'lei'                     => 'nullable|string|size:20',
            'bic_swift'               => 'nullable|string|max:11',
            'credit_limit'            => 'nullable|numeric|min:0',
            'credit_limit_currency_id'=> 'nullable|exists:currencies,id',
            'kyc_status'              => 'nullable|in:Pending,Approved,Expired,Suspended',
            'kyc_review_date'         => 'nullable|date',
            'regulatory_class'        => 'nullable|in:FC,NFC,NFC+,Third-Country',
        ]);
        Party::create($data);
        return redirect()->route('master.parties.index')->with('success', 'Party created.');
    }

    public function show(Party $party)
    {
        $party->load('parent', 'children', 'creditLimitCurrency', 'portfolios');
        return view('master.parties.show', compact('party'));
    }

    public function edit(Party $party)
    {
        return view('master.parties.edit', [
            'party'      => $party,
            'parents'    => Party::whereIn('party_type', ['Group', 'LE'])->where('id', '!=', $party->id)->orderBy('short_name')->get(),
            'currencies' => Currency::orderBy('code')->get(),
        ]);
    }

    public function update(Request $request, Party $party)
    {
        $data = $request->validate([
            'party_type'              => 'required|in:Group,LE,BU',
            'internal_external'       => 'required|in:Internal,External',
            'parent_id'               => 'nullable|exists:parties,id',
            'short_name'              => 'required|string|max:32|unique:parties,short_name,'.$party->id,
            'long_name'               => 'required|string|max:255',
            'status'                  => 'required|in:Auth Pending,Authorized,Do Not Use',
            'lei'                     => 'nullable|string|size:20',
            'bic_swift'               => 'nullable|string|max:11',
            'credit_limit'            => 'nullable|numeric|min:0',
            'credit_limit_currency_id'=> 'nullable|exists:currencies,id',
            'kyc_status'              => 'nullable|in:Pending,Approved,Expired,Suspended',
            'kyc_review_date'         => 'nullable|date',
            'regulatory_class'        => 'nullable|in:FC,NFC,NFC+,Third-Country',
        ]);
        $data['version'] = $party->version + 1;
        $party->update($data);
        return redirect()->route('master.parties.show', $party)->with('success', 'Party updated (v'.$data['version'].').');
    }

    public function destroy(Party $party)
    {
        $party->delete();
        return redirect()->route('master.parties.index')->with('success', 'Party deleted.');
    }
}
