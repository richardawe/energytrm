<?php
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\CreditRating;
use App\Models\Party;
use Illuminate\Http\Request;

class CreditRatingController extends Controller
{
    public function create(Party $party)
    {
        return view('master.credit-ratings.create', compact('party'));
    }

    public function store(Request $request, Party $party)
    {
        $data = $request->validate([
            'source'         => 'required|string|max:100',
            'rating'         => 'required|string|max:20',
            'effective_date' => 'nullable|date',
            'notes'          => 'nullable|string',
        ]);

        $data['party_id'] = $party->id;
        CreditRating::create($data);

        return redirect()->route('master.parties.show', $party)->with('success', 'Credit rating added.');
    }

    public function edit(Party $party, CreditRating $creditRating)
    {
        return view('master.credit-ratings.edit', compact('party', 'creditRating'));
    }

    public function update(Request $request, Party $party, CreditRating $creditRating)
    {
        $data = $request->validate([
            'source'         => 'required|string|max:100',
            'rating'         => 'required|string|max:20',
            'effective_date' => 'nullable|date',
            'notes'          => 'nullable|string',
        ]);

        $creditRating->update($data);

        return redirect()->route('master.parties.show', $party)->with('success', 'Credit rating updated.');
    }

    public function destroy(Party $party, CreditRating $creditRating)
    {
        $creditRating->delete();
        return redirect()->route('master.parties.show', $party)->with('success', 'Credit rating deleted.');
    }
}
