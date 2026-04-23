<?php
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\GoverningBody;
use Illuminate\Http\Request;

class GoverningBodyController extends Controller
{
    public function index()
    {
        $governingBodies = GoverningBody::orderBy('name')->paginate(25);
        return view('master.governing-bodies.index', compact('governingBodies'));
    }

    public function create()
    {
        return view('master.governing-bodies.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:150',
            'jurisdiction' => 'nullable|string|max:100',
            'country'      => 'nullable|string|max:100',
            'is_active'    => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        GoverningBody::create($data);
        return redirect()->route('master.governing-bodies.index')->with('success', 'Governing body created.');
    }

    public function show(GoverningBody $governingBody)
    {
        return view('master.governing-bodies.show', compact('governingBody'));
    }

    public function edit(GoverningBody $governingBody)
    {
        return view('master.governing-bodies.edit', compact('governingBody'));
    }

    public function update(Request $request, GoverningBody $governingBody)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:150',
            'jurisdiction' => 'nullable|string|max:100',
            'country'      => 'nullable|string|max:100',
            'is_active'    => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $governingBody->update($data);
        return redirect()->route('master.governing-bodies.show', $governingBody)->with('success', 'Governing body updated.');
    }

    public function destroy(GoverningBody $governingBody)
    {
        $governingBody->delete();
        return redirect()->route('master.governing-bodies.index')->with('success', 'Governing body deleted.');
    }
}
