<?php
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\ContractType;
use Illuminate\Http\Request;

class ContractTypeController extends Controller
{
    public function index()
    {
        $contractTypes = ContractType::orderBy('name')->paginate(25);
        return view('master.contract-types.index', compact('contractTypes'));
    }

    public function create()
    {
        return view('master.contract-types.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'code'        => 'required|string|max:20|unique:contract_types,code',
            'description' => 'nullable|string',
            'incoterm'    => 'nullable|string|max:20',
            'is_active'   => 'boolean',
        ]);
        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = $request->boolean('is_active', true);
        ContractType::create($data);
        return redirect()->route('master.contract-types.index')->with('success', 'Contract type created.');
    }

    public function show(ContractType $contractType)
    {
        return view('master.contract-types.show', compact('contractType'));
    }

    public function edit(ContractType $contractType)
    {
        return view('master.contract-types.edit', compact('contractType'));
    }

    public function update(Request $request, ContractType $contractType)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'code'        => 'required|string|max:20|unique:contract_types,code,' . $contractType->id,
            'description' => 'nullable|string',
            'incoterm'    => 'nullable|string|max:20',
            'is_active'   => 'boolean',
        ]);
        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = $request->boolean('is_active', true);
        $contractType->update($data);
        return redirect()->route('master.contract-types.show', $contractType)->with('success', 'Contract type updated.');
    }

    public function destroy(ContractType $contractType)
    {
        $contractType->delete();
        return redirect()->route('master.contract-types.index')->with('success', 'Contract type deleted.');
    }
}
