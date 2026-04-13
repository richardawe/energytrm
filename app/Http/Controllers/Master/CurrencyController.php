<?php
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function index()
    {
        $currencies = Currency::orderBy('code')->paginate(25);
        return view('master.currencies.index', compact('currencies'));
    }

    public function create()
    {
        return view('master.currencies.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'           => 'required|string|size:3|unique:currencies,code',
            'name'           => 'required|string|max:100',
            'symbol'         => 'nullable|string|max:10',
            'fx_rate_to_usd' => 'required|numeric|min:0',
            'is_active'      => 'boolean',
        ]);
        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = $request->boolean('is_active', true);
        Currency::create($data);
        return redirect()->route('master.currencies.index')->with('success', 'Currency created.');
    }

    public function show(Currency $currency)
    {
        return view('master.currencies.show', compact('currency'));
    }

    public function edit(Currency $currency)
    {
        return view('master.currencies.edit', compact('currency'));
    }

    public function update(Request $request, Currency $currency)
    {
        $data = $request->validate([
            'code'           => 'required|string|size:3|unique:currencies,code,'.$currency->id,
            'name'           => 'required|string|max:100',
            'symbol'         => 'nullable|string|max:10',
            'fx_rate_to_usd' => 'required|numeric|min:0',
            'is_active'      => 'boolean',
        ]);
        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = $request->boolean('is_active', true);
        $currency->update($data);
        return redirect()->route('master.currencies.index')->with('success', 'Currency updated.');
    }

    public function destroy(Currency $currency)
    {
        $currency->delete();
        return redirect()->route('master.currencies.index')->with('success', 'Currency deleted.');
    }
}
