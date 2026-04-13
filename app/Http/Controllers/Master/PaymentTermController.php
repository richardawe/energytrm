<?php
namespace App\Http\Controllers\Master;
use App\Http\Controllers\Controller;
use App\Models\PaymentTerm;
use Illuminate\Http\Request;

class PaymentTermController extends Controller
{
    public function index()    { return view('master.payment-terms.index', ['paymentTerms' => PaymentTerm::orderBy('name')->paginate(25)]); }
    public function create()   { return view('master.payment-terms.create'); }
    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:100|unique:payment_terms', 'days_net' => 'required|integer|min:0', 'description' => 'nullable|string|max:255']);
        $data['is_active'] = $request->boolean('is_active', true);
        PaymentTerm::create($data);
        return redirect()->route('master.payment-terms.index')->with('success', 'Payment term created.');
    }
    public function show(PaymentTerm $paymentTerm)  { return view('master.payment-terms.show', compact('paymentTerm')); }
    public function edit(PaymentTerm $paymentTerm)  { return view('master.payment-terms.edit', compact('paymentTerm')); }
    public function update(Request $request, PaymentTerm $paymentTerm)
    {
        $data = $request->validate(['name' => 'required|string|max:100|unique:payment_terms,name,'.$paymentTerm->id, 'days_net' => 'required|integer|min:0', 'description' => 'nullable|string|max:255']);
        $data['is_active'] = $request->boolean('is_active', true);
        $paymentTerm->update($data);
        return redirect()->route('master.payment-terms.index')->with('success', 'Payment term updated.');
    }
    public function destroy(PaymentTerm $paymentTerm) { $paymentTerm->delete(); return redirect()->route('master.payment-terms.index')->with('success', 'Deleted.'); }
}
