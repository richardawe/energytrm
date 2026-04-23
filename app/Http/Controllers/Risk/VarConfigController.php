<?php

namespace App\Http\Controllers\Risk;

use App\Http\Controllers\Controller;
use App\Models\VarConfiguration;
use Illuminate\Http\Request;

class VarConfigController extends Controller
{
    public function index()
    {
        $configs = VarConfiguration::orderByDesc('is_active')->orderBy('name')->get();
        return view('risk.var-config.index', compact('configs'));
    }

    public function create()
    {
        return view('risk.var-config.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                 => 'required|string|max:100',
            'lookback_period_days' => 'required|integer|min:1|max:2000',
            'holding_period_days'  => 'required|in:1,10',
            'var_method'           => 'required|in:Historical Simulation,Parametric,Monte Carlo',
            'confidence_level'     => 'required|numeric|min:0.9|max:0.9999',
            'is_active'            => 'boolean',
        ]);

        $data['is_active']  = $request->boolean('is_active', true);
        $data['created_by'] = auth()->id();

        VarConfiguration::create($data);

        return redirect()->route('risk.var-config.index')->with('success', 'VaR configuration created.');
    }

    public function edit(VarConfiguration $varConfig)
    {
        return view('risk.var-config.edit', compact('varConfig'));
    }

    public function update(Request $request, VarConfiguration $varConfig)
    {
        $data = $request->validate([
            'name'                 => 'required|string|max:100',
            'lookback_period_days' => 'required|integer|min:1|max:2000',
            'holding_period_days'  => 'required|in:1,10',
            'var_method'           => 'required|in:Historical Simulation,Parametric,Monte Carlo',
            'confidence_level'     => 'required|numeric|min:0.9|max:0.9999',
            'is_active'            => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $varConfig->update($data);

        return redirect()->route('risk.var-config.index')->with('success', 'VaR configuration updated.');
    }

    public function destroy(VarConfiguration $varConfig)
    {
        $varConfig->delete();
        return redirect()->route('risk.var-config.index')->with('success', 'VaR configuration deleted.');
    }
}
