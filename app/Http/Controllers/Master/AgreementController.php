<?php
namespace App\Http\Controllers\Master;
use App\Http\Controllers\Controller;
use App\Models\Agreement;
use App\Models\Party;
use App\Models\PaymentTerm;
use Illuminate\Http\Request;

class AgreementController extends Controller
{
    public function index()  { return view('master.agreements.index', ['agreements' => Agreement::with(['internalParty','counterparty','paymentTerms'])->orderBy('name')->paginate(25)]); }
    public function create() { return view('master.agreements.create', ['internalParties' => Party::internal()->authorized()->orderBy('short_name')->get(), 'counterparties' => Party::external()->authorized()->orderBy('short_name')->get(), 'paymentTerms' => PaymentTerm::where('is_active',true)->orderBy('name')->get()]); }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:150', 'internal_party_id' => 'nullable|exists:parties,id', 'counterparty_id' => 'nullable|exists:parties,id', 'payment_terms_id' => 'nullable|exists:payment_terms,id', 'effective_date' => 'nullable|date', 'expiry_date' => 'nullable|date|after_or_equal:effective_date', 'notes' => 'nullable|string', 'status' => 'required|in:Auth Pending,Authorized,Do Not Use']);
        Agreement::create($data);
        return redirect()->route('master.agreements.index')->with('success', 'Agreement created.');
    }
    public function show(Agreement $agreement)  { return view('master.agreements.show', ['agreement' => $agreement->load(['internalParty','counterparty','paymentTerms'])]); }
    public function edit(Agreement $agreement)  { return view('master.agreements.edit', ['agreement' => $agreement, 'internalParties' => Party::internal()->authorized()->orderBy('short_name')->get(), 'counterparties' => Party::external()->authorized()->orderBy('short_name')->get(), 'paymentTerms' => PaymentTerm::where('is_active',true)->orderBy('name')->get()]); }
    public function update(Request $request, Agreement $agreement)
    {
        $data = $request->validate(['name' => 'required|string|max:150', 'internal_party_id' => 'nullable|exists:parties,id', 'counterparty_id' => 'nullable|exists:parties,id', 'payment_terms_id' => 'nullable|exists:payment_terms,id', 'effective_date' => 'nullable|date', 'expiry_date' => 'nullable|date|after_or_equal:effective_date', 'notes' => 'nullable|string', 'status' => 'required|in:Auth Pending,Authorized,Do Not Use']);
        $data['version'] = $agreement->version + 1;
        $agreement->update($data);
        return redirect()->route('master.agreements.index')->with('success', 'Agreement updated.');
    }
    public function destroy(Agreement $agreement) { $agreement->delete(); return redirect()->route('master.agreements.index')->with('success', 'Deleted.'); }
}
