<?php
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Commodity;
use Illuminate\Http\Request;

class CommodityController extends Controller
{
    public function index()
    {
        $commodities = Commodity::orderBy('name')->paginate(25);
        return view('master.commodities.index', compact('commodities'));
    }

    public function create()
    {
        return view('master.commodities.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:150',
            'commodity_group' => 'required|in:Energy,Metal,Agricultural,Other',
            'description'     => 'nullable|string',
            'is_active'       => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        Commodity::create($data);
        return redirect()->route('master.commodities.index')->with('success', 'Commodity created.');
    }

    public function show(Commodity $commodity)
    {
        return view('master.commodities.show', compact('commodity'));
    }

    public function edit(Commodity $commodity)
    {
        return view('master.commodities.edit', compact('commodity'));
    }

    public function update(Request $request, Commodity $commodity)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:150',
            'commodity_group' => 'required|in:Energy,Metal,Agricultural,Other',
            'description'     => 'nullable|string',
            'is_active'       => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $commodity->update($data);
        return redirect()->route('master.commodities.show', $commodity)->with('success', 'Commodity updated.');
    }

    public function destroy(Commodity $commodity)
    {
        $commodity->delete();
        return redirect()->route('master.commodities.index')->with('success', 'Commodity deleted.');
    }
}
