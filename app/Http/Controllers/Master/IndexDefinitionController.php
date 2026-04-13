<?php
namespace App\Http\Controllers\Master;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\IndexDefinition;
use App\Models\Uom;
use Illuminate\Http\Request;

class IndexDefinitionController extends Controller
{
    public function index()  { return view('master.indices.index', ['indices' => IndexDefinition::with(['baseCurrency','uom','latestPrice'])->orderBy('index_name')->paginate(25)]); }
    public function create() { return view('master.indices.create', ['currencies' => Currency::orderBy('code')->get(), 'uoms' => Uom::where('is_active',true)->orderBy('code')->get()]); }

    public function store(Request $request)
    {
        $data = $request->validate([
            'index_name'       => 'required|string|max:100',
            'market'           => 'nullable|string|max:100',
            'index_group'      => 'nullable|string|max:100',
            'format'           => 'required|in:Daily,Monthly,Quarterly,Annual',
            'class'            => 'nullable|string|max:50',
            'base_currency_id' => 'nullable|exists:currencies,id',
            'uom_id'           => 'nullable|exists:uoms,id',
            'status'           => 'required|in:Custom,Official,Template',
            'rec_status'       => 'required|in:Auth Pending,Authorized,Do Not Use',
        ]);
        IndexDefinition::create($data);
        return redirect()->route('master.indices.index')->with('success', 'Index created.');
    }

    public function show(IndexDefinition $index)  { return view('master.indices.show', ['index' => $index->load(['baseCurrency','uom','gridPoints'])]); }
    public function edit(IndexDefinition $index)  { return view('master.indices.edit', ['index' => $index, 'currencies' => Currency::orderBy('code')->get(), 'uoms' => Uom::where('is_active',true)->orderBy('code')->get()]); }

    public function update(Request $request, IndexDefinition $index)
    {
        $data = $request->validate([
            'index_name'       => 'required|string|max:100',
            'market'           => 'nullable|string|max:100',
            'index_group'      => 'nullable|string|max:100',
            'format'           => 'required|in:Daily,Monthly,Quarterly,Annual',
            'class'            => 'nullable|string|max:50',
            'base_currency_id' => 'nullable|exists:currencies,id',
            'uom_id'           => 'nullable|exists:uoms,id',
            'status'           => 'required|in:Custom,Official,Template',
            'rec_status'       => 'required|in:Auth Pending,Authorized,Do Not Use',
        ]);
        // Handle grid point addition via the show-page modal
        if ($request->boolean('add_grid_point')) {
            $request->validate([
                'grid_date'  => 'required|date',
                'grid_price' => 'required|numeric|min:0',
            ]);
            \App\Models\IndexGridPoint::updateOrCreate(
                ['index_id' => $index->id, 'price_date' => $request->grid_date],
                ['price' => $request->grid_price, 'entered_by' => auth()->id()]
            );
            return redirect()->route('master.indices.show', $index)->with('success', 'Price added.');
        }

        $data['version'] = $index->version + 1;
        $index->update($data);
        return redirect()->route('master.indices.show', $index)->with('success', 'Index updated.');
    }

    public function destroy(IndexDefinition $index) { $index->delete(); return redirect()->route('master.indices.index')->with('success', 'Deleted.'); }
}
