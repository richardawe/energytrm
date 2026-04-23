<?php

namespace App\Http\Controllers\Risk;

use App\Http\Controllers\Controller;
use App\Models\IndexDefinition;
use App\Models\StressScenario;
use App\Models\StressScenarioShock;
use Illuminate\Http\Request;

class StressScenarioController extends Controller
{
    public function index()
    {
        $scenarios = StressScenario::withCount('shocks')->orderBy('name')->paginate(20);
        return view('risk.stress-scenarios.index', compact('scenarios'));
    }

    public function create()
    {
        $indices = IndexDefinition::orderBy('index_name')->get();
        return view('risk.stress-scenarios.create', compact('indices'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                        => 'required|string|max:150',
            'description'                 => 'nullable|string',
            'is_active'                   => 'boolean',
            'shocks'                      => 'array',
            'shocks.*.index_id'           => 'required|exists:index_definitions,id',
            'shocks.*.price_shock_pct'    => 'required|numeric',
        ]);

        $scenario = StressScenario::create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active'   => $request->boolean('is_active', true),
            'created_by'  => auth()->id(),
        ]);

        foreach ($data['shocks'] ?? [] as $shock) {
            StressScenarioShock::create([
                'stress_scenario_id' => $scenario->id,
                'index_id'           => $shock['index_id'],
                'price_shock_pct'    => $shock['price_shock_pct'],
            ]);
        }

        return redirect()->route('risk.stress-scenarios.index')->with('success', 'Stress scenario created.');
    }

    public function show(StressScenario $stressScenario)
    {
        $stressScenario->load('shocks.index');
        return view('risk.stress-scenarios.show', compact('stressScenario'));
    }

    public function edit(StressScenario $stressScenario)
    {
        $stressScenario->load('shocks');
        $indices = IndexDefinition::orderBy('index_name')->get();
        return view('risk.stress-scenarios.edit', compact('stressScenario', 'indices'));
    }

    public function update(Request $request, StressScenario $stressScenario)
    {
        $data = $request->validate([
            'name'                        => 'required|string|max:150',
            'description'                 => 'nullable|string',
            'is_active'                   => 'boolean',
            'shocks'                      => 'array',
            'shocks.*.index_id'           => 'required|exists:index_definitions,id',
            'shocks.*.price_shock_pct'    => 'required|numeric',
        ]);

        $stressScenario->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        // Sync shocks: delete and recreate
        $stressScenario->shocks()->delete();

        foreach ($data['shocks'] ?? [] as $shock) {
            StressScenarioShock::create([
                'stress_scenario_id' => $stressScenario->id,
                'index_id'           => $shock['index_id'],
                'price_shock_pct'    => $shock['price_shock_pct'],
            ]);
        }

        return redirect()->route('risk.stress-scenarios.show', $stressScenario)->with('success', 'Stress scenario updated.');
    }

    public function destroy(StressScenario $stressScenario)
    {
        $stressScenario->delete();
        return redirect()->route('risk.stress-scenarios.index')->with('success', 'Stress scenario deleted.');
    }
}
