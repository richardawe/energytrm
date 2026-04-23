<?php
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Exchange;
use Illuminate\Http\Request;

class ExchangeController extends Controller
{
    public function index()
    {
        $exchanges = Exchange::orderBy('name')->paginate(25);
        return view('master.exchanges.index', compact('exchanges'));
    }

    public function create()
    {
        return view('master.exchanges.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'      => 'required|string|max:20|unique:exchanges,code',
            'name'      => 'required|string|max:150',
            'timezone'  => 'nullable|string|max:50',
            'country'   => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);
        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = $request->boolean('is_active', true);
        Exchange::create($data);
        return redirect()->route('master.exchanges.index')->with('success', 'Exchange created.');
    }

    public function show(Exchange $exchange)
    {
        return view('master.exchanges.show', compact('exchange'));
    }

    public function edit(Exchange $exchange)
    {
        return view('master.exchanges.edit', compact('exchange'));
    }

    public function update(Request $request, Exchange $exchange)
    {
        $data = $request->validate([
            'code'      => 'required|string|max:20|unique:exchanges,code,' . $exchange->id,
            'name'      => 'required|string|max:150',
            'timezone'  => 'nullable|string|max:50',
            'country'   => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);
        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = $request->boolean('is_active', true);
        $exchange->update($data);
        return redirect()->route('master.exchanges.show', $exchange)->with('success', 'Exchange updated.');
    }

    public function destroy(Exchange $exchange)
    {
        $exchange->delete();
        return redirect()->route('master.exchanges.index')->with('success', 'Exchange deleted.');
    }
}
