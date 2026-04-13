<?php
namespace App\Http\Controllers\Master;
use App\Http\Controllers\Controller;
use App\Models\Incoterm;
use Illuminate\Http\Request;

class IncotermController extends Controller
{
    public function index()   { return view('master.incoterms.index', ['incoterms' => Incoterm::orderBy('code')->paginate(25)]); }
    public function create()  { return view('master.incoterms.create'); }
    public function store(Request $request)
    {
        $data = $request->validate(['code' => 'required|string|max:10|unique:incoterms', 'name' => 'required|string|max:100', 'description' => 'nullable|string']);
        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = $request->boolean('is_active', true);
        Incoterm::create($data);
        return redirect()->route('master.incoterms.index')->with('success', 'Incoterm created.');
    }
    public function show(Incoterm $incoterm)  { return view('master.incoterms.show', compact('incoterm')); }
    public function edit(Incoterm $incoterm)  { return view('master.incoterms.edit', compact('incoterm')); }
    public function update(Request $request, Incoterm $incoterm)
    {
        $data = $request->validate(['code' => 'required|string|max:10|unique:incoterms,code,'.$incoterm->id, 'name' => 'required|string|max:100', 'description' => 'nullable|string']);
        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = $request->boolean('is_active', true);
        $incoterm->update($data);
        return redirect()->route('master.incoterms.index')->with('success', 'Incoterm updated.');
    }
    public function destroy(Incoterm $incoterm) { $incoterm->delete(); return redirect()->route('master.incoterms.index')->with('success', 'Deleted.'); }
}
