<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Pipeline;
use App\Models\PipelineLocation;
use App\Models\PipelineZone;
use Illuminate\Http\Request;

class PipelineController extends Controller
{
    public function index()
    {
        $pipelines = Pipeline::with(['zones.locations'])->orderBy('code')->get();
        return view('master.pipelines.index', compact('pipelines'));
    }

    public function create()
    {
        return view('master.pipelines.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'           => 'required|string|max:20|unique:pipelines,code',
            'name'           => 'required|string|max:100',
            'commodity_type' => 'required|in:Oil,Gas,LNG,Power',
            'operator'       => 'nullable|string|max:100',
            'country'        => 'nullable|string|max:50',
            'status'         => 'required|in:Authorized,Do Not Use',
        ]);

        Pipeline::create($data + ['version' => 0]);

        return redirect()->route('master.pipelines.index')
            ->with('success', "Pipeline {$data['code']} created.");
    }

    public function show(Pipeline $pipeline)
    {
        $pipeline->load('zones.locations');
        return view('master.pipelines.show', compact('pipeline'));
    }

    public function edit(Pipeline $pipeline)
    {
        $pipeline->load('zones.locations');
        return view('master.pipelines.edit', compact('pipeline'));
    }

    public function update(Request $request, Pipeline $pipeline)
    {
        $data = $request->validate([
            'code'           => 'required|string|max:20|unique:pipelines,code,' . $pipeline->id,
            'name'           => 'required|string|max:100',
            'commodity_type' => 'required|in:Oil,Gas,LNG,Power',
            'operator'       => 'nullable|string|max:100',
            'country'        => 'nullable|string|max:50',
            'status'         => 'required|in:Authorized,Do Not Use',
        ]);

        $pipeline->update($data);

        return redirect()->route('master.pipelines.show', $pipeline)
            ->with('success', 'Pipeline updated.');
    }

    // ── Zone sub-resources ────────────────────────────────────────────────────

    public function storeZone(Request $request, Pipeline $pipeline)
    {
        $data = $request->validate([
            'zone_code' => 'required|string|max:20',
            'zone_name' => 'required|string|max:100',
            'status'    => 'required|in:Authorized,Do Not Use',
        ]);

        $pipeline->zones()->create($data);

        return redirect()->route('master.pipelines.show', $pipeline)
            ->with('success', "Zone {$data['zone_code']} added.");
    }

    public function storeLocation(Request $request, Pipeline $pipeline, PipelineZone $zone)
    {
        $data = $request->validate([
            'location_code' => 'required|string|max:30',
            'location_name' => 'required|string|max:100',
            'location_type' => 'required|in:Receipt,Delivery,Both',
            'status'        => 'required|in:Authorized,Do Not Use',
        ]);

        $zone->locations()->create($data);

        return redirect()->route('master.pipelines.show', $pipeline)
            ->with('success', "Location {$data['location_code']} added.");
    }
}
