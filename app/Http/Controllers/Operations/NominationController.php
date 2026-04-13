<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Nomination;
use App\Models\Trade;
use App\Models\Uom;
use Illuminate\Http\Request;

class NominationController extends Controller
{
    public function index(Request $request)
    {
        $query = Nomination::with(['trade.counterparty', 'trade.product', 'uom'])->latest('gas_day')->latest('id');

        if ($request->filled('status')) {
            $query->where('nomination_status', $request->status);
        }
        if ($request->filled('gas_day')) {
            $query->where('gas_day', $request->gas_day);
        }

        $nominations = $query->paginate(25)->withQueryString();
        return view('operations.nominations.index', compact('nominations'));
    }

    public function create(Request $request)
    {
        $trade  = $request->filled('trade_id') ? Trade::findOrFail($request->trade_id) : null;
        $trades = Trade::whereIn('trade_status', ['Validated', 'Settled'])->orderBy('deal_number')->get();
        $uoms   = Uom::orderBy('code')->get();
        return view('operations.nominations.create', compact('trade', 'trades', 'uoms'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'trade_id'          => 'required|exists:trades,id',
            'gas_day'           => 'required|date',
            'pipeline_operator' => 'nullable|string|max:100',
            'delivery_point'    => 'nullable|string|max:100',
            'nominated_volume'  => 'required|numeric|min:0.0001',
            'confirmed_volume'  => 'nullable|numeric|min:0',
            'uom_id'            => 'required|exists:uoms,id',
            'nomination_status' => 'required|in:Pending,Confirmed,Matched,Unmatched',
            'comments'          => 'nullable|string|max:1000',
        ]);

        $data['nomination_number'] = Nomination::nextNominationNumber();
        $data['created_by']        = auth()->id();

        Nomination::create($data);

        return redirect()->route('operations.nominations.index')
            ->with('success', 'Nomination created.');
    }

    public function edit(Nomination $nomination)
    {
        $uoms = Uom::orderBy('code')->get();
        return view('operations.nominations.edit', compact('nomination', 'uoms'));
    }

    public function update(Request $request, Nomination $nomination)
    {
        $data = $request->validate([
            'gas_day'           => 'required|date',
            'pipeline_operator' => 'nullable|string|max:100',
            'delivery_point'    => 'nullable|string|max:100',
            'nominated_volume'  => 'required|numeric|min:0.0001',
            'confirmed_volume'  => 'nullable|numeric|min:0',
            'uom_id'            => 'required|exists:uoms,id',
            'nomination_status' => 'required|in:Pending,Confirmed,Matched,Unmatched',
            'comments'          => 'nullable|string|max:1000',
        ]);

        $nomination->update($data);

        return redirect()->route('operations.nominations.index')
            ->with('success', 'Nomination updated.');
    }
}
