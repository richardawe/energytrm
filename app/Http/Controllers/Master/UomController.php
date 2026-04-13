<?php
namespace App\Http\Controllers\Master;
use App\Http\Controllers\Controller;
use App\Models\Uom;
use Illuminate\Http\Request;

class UomController extends Controller
{
    public function index()   { return view('master.uoms.index', ['uoms' => Uom::orderBy('code')->paginate(25)]); }
    public function create()  { return view('master.uoms.create'); }
    public function store(Request $request)
    {
        $data = $request->validate(['code' => 'required|string|max:20|unique:uoms', 'description' => 'required|string|max:100', 'conversion_factor' => 'required|numeric|min:0', 'base_unit' => 'nullable|string|max:20']);
        $data['is_active'] = $request->boolean('is_active', true);
        Uom::create($data);
        return redirect()->route('master.uoms.index')->with('success', 'UOM created.');
    }
    public function show(Uom $uom)  { return view('master.uoms.show', compact('uom')); }
    public function edit(Uom $uom)  { return view('master.uoms.edit', compact('uom')); }
    public function update(Request $request, Uom $uom)
    {
        $data = $request->validate(['code' => 'required|string|max:20|unique:uoms,code,'.$uom->id, 'description' => 'required|string|max:100', 'conversion_factor' => 'required|numeric|min:0', 'base_unit' => 'nullable|string|max:20']);
        $data['is_active'] = $request->boolean('is_active', true);
        $uom->update($data);
        return redirect()->route('master.uoms.index')->with('success', 'UOM updated.');
    }
    public function destroy(Uom $uom) { $uom->delete(); return redirect()->route('master.uoms.index')->with('success', 'Deleted.'); }
}
