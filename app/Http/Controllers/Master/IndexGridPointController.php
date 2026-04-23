<?php
namespace App\Http\Controllers\Master;
use App\Http\Controllers\Controller;
use App\Models\IndexDefinition;
use App\Models\IndexGridPoint;
use Illuminate\Http\Request;

class IndexGridPointController extends Controller
{
    public function create(IndexDefinition $index) {
        return view('master.grid-points.create', compact('index'));
    }
    public function store(Request $request, IndexDefinition $index) {
        $data = $request->validate([
            'grid_point_label'    => 'nullable|string|max:100',
            'instrument_category' => 'nullable|string|max:50',
            'priority_level'      => 'nullable|integer|min:1|max:8',
            'price_date'          => 'required|date',
            'price'               => 'required|numeric',
            'start_time'          => 'nullable|date_format:H:i',
            'end_time'            => 'nullable|date_format:H:i',
            'delta_shift'         => 'nullable|numeric',
            'sensitivity'         => 'nullable|in:effective,raw,no',
        ]);
        $data['index_id'] = $index->id;
        IndexGridPoint::create($data);
        return redirect()->route('master.indices.show', $index)->with('success', 'Grid point added.');
    }
    public function edit(IndexDefinition $index, IndexGridPoint $gridPoint) {
        return view('master.grid-points.edit', compact('index', 'gridPoint'));
    }
    public function update(Request $request, IndexDefinition $index, IndexGridPoint $gridPoint) {
        $data = $request->validate([
            'grid_point_label'    => 'nullable|string|max:100',
            'instrument_category' => 'nullable|string|max:50',
            'priority_level'      => 'nullable|integer|min:1|max:8',
            'price_date'          => 'required|date',
            'price'               => 'required|numeric',
            'start_time'          => 'nullable|date_format:H:i',
            'end_time'            => 'nullable|date_format:H:i',
            'delta_shift'         => 'nullable|numeric',
            'sensitivity'         => 'nullable|in:effective,raw,no',
        ]);
        $gridPoint->update($data);
        return redirect()->route('master.indices.show', $index)->with('success', 'Grid point updated.');
    }
    public function destroy(IndexDefinition $index, IndexGridPoint $gridPoint) {
        $gridPoint->delete();
        return redirect()->route('master.indices.show', $index)->with('success', 'Grid point deleted.');
    }
}
