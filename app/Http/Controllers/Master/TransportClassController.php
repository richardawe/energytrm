<?php
namespace App\Http\Controllers\Master;
use App\Http\Controllers\Controller;
use App\Models\TransportClass;
use Illuminate\Http\Request;

class TransportClassController extends Controller
{
    public function index()   { return view('master.transport-classes.index', ['transportClasses' => TransportClass::orderBy('name')->paginate(25)]); }
    public function create()  { return view('master.transport-classes.create'); }
    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:100|unique:transport_classes', 'description' => 'nullable|string|max:255']);
        $data['is_active'] = $request->boolean('is_active', true);
        TransportClass::create($data);
        return redirect()->route('master.transport-classes.index')->with('success', 'Transport class created.');
    }
    public function show(TransportClass $transportClass)  { return view('master.transport-classes.show', compact('transportClass')); }
    public function edit(TransportClass $transportClass)  { return view('master.transport-classes.edit', compact('transportClass')); }
    public function update(Request $request, TransportClass $transportClass)
    {
        $data = $request->validate(['name' => 'required|string|max:100|unique:transport_classes,name,'.$transportClass->id, 'description' => 'nullable|string|max:255']);
        $data['is_active'] = $request->boolean('is_active', true);
        $transportClass->update($data);
        return redirect()->route('master.transport-classes.index')->with('success', 'Updated.');
    }
    public function destroy(TransportClass $transportClass) { $transportClass->delete(); return redirect()->route('master.transport-classes.index')->with('success', 'Deleted.'); }
}
