<?php

namespace App\Http\Controllers\Risk;

use App\Http\Controllers\Controller;
use App\Models\CreditWarningThreshold;
use App\Models\Party;
use Illuminate\Http\Request;

class CreditWarningController extends Controller
{
    public function index()
    {
        $thresholds = CreditWarningThreshold::with('party')->orderBy('id')->get();
        return view('risk.credit-warnings.index', compact('thresholds'));
    }

    public function create()
    {
        $parties = Party::where('party_type', 'BU')
            ->where('internal_external', 'External')
            ->where('status', 'Authorized')
            ->orderBy('short_name')
            ->get();
        return view('risk.credit-warnings.create', compact('parties'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'party_id'              => 'required|exists:parties,id',
            'warning_threshold_pct' => 'required|numeric|min:0|max:100',
            'breach_threshold_pct'  => 'required|numeric|min:0|max:100',
            'is_active'             => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        CreditWarningThreshold::create($data);

        return redirect()->route('risk.credit-warnings.index')->with('success', 'Credit warning threshold created.');
    }

    public function edit(CreditWarningThreshold $threshold)
    {
        $parties = Party::where('party_type', 'BU')
            ->where('internal_external', 'External')
            ->where('status', 'Authorized')
            ->orderBy('short_name')
            ->get();
        return view('risk.credit-warnings.edit', compact('threshold', 'parties'));
    }

    public function update(Request $request, CreditWarningThreshold $threshold)
    {
        $data = $request->validate([
            'party_id'              => 'required|exists:parties,id',
            'warning_threshold_pct' => 'required|numeric|min:0|max:100',
            'breach_threshold_pct'  => 'required|numeric|min:0|max:100',
            'is_active'             => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $threshold->update($data);

        return redirect()->route('risk.credit-warnings.index')->with('success', 'Credit warning threshold updated.');
    }

    public function destroy(CreditWarningThreshold $threshold)
    {
        $threshold->delete();
        return redirect()->route('risk.credit-warnings.index')->with('success', 'Credit warning threshold deleted.');
    }
}
