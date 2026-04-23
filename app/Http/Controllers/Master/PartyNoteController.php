<?php
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Party;
use App\Models\PartyNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PartyNoteController extends Controller
{
    public function create(Party $party)
    {
        return view('master.party-notes.create', compact('party'));
    }

    public function store(Request $request, Party $party)
    {
        $data = $request->validate([
            'note_type' => 'nullable|string|max:100',
            'title'     => 'required|string|max:200',
            'body'      => 'required|string',
            'note_date' => 'required|date',
        ]);

        $data['party_id']   = $party->id;
        $data['created_by'] = Auth::id();
        $data['version']    = 0;

        PartyNote::create($data);

        return redirect()->route('master.parties.show', $party)->with('success', 'Note added.');
    }

    public function edit(Party $party, PartyNote $note)
    {
        return view('master.party-notes.edit', compact('party', 'note'));
    }

    public function update(Request $request, Party $party, PartyNote $note)
    {
        $data = $request->validate([
            'note_type' => 'nullable|string|max:100',
            'title'     => 'required|string|max:200',
            'body'      => 'required|string',
            'note_date' => 'required|date',
        ]);

        $data['version'] = $note->version + 1;
        $note->update($data);

        return redirect()->route('master.parties.show', $party)->with('success', 'Note updated.');
    }

    public function destroy(Party $party, PartyNote $note)
    {
        $note->delete();
        return redirect()->route('master.parties.show', $party)->with('success', 'Note deleted.');
    }
}
