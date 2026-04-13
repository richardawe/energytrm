<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\EobChecklist;
use App\Models\Party;
use Illuminate\Http\Request;

class EobChecklistController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', today()->toDateString());
        $businessUnits = Party::where('internal_external', 'Internal')
            ->where('party_type', 'BU')
            ->where('status', 'Authorized')
            ->orderBy('short_name')
            ->get();

        // Ensure a checklist record exists for each BU for today
        foreach ($businessUnits as $bu) {
            $checklist = EobChecklist::firstOrCreate(
                ['checklist_date' => $date, 'business_unit_id' => $bu->id],
            );
            $checklist->refreshItems();
        }

        $checklists = EobChecklist::with(['businessUnit', 'signedOffBy'])
            ->where('checklist_date', $date)
            ->get();

        return view('operations.eob.index', compact('checklists', 'date', 'businessUnits'));
    }

    public function signOff(EobChecklist $eobChecklist)
    {
        if ($eobChecklist->signed_off) {
            return back()->with('error', 'Already signed off.');
        }

        $eobChecklist->refreshItems();
        $eobChecklist->update([
            'signed_off'    => true,
            'signed_off_by' => auth()->id(),
            'signed_off_at' => now(),
        ]);

        return back()->with('success', 'EoB checklist signed off.');
    }

    public function reset(EobChecklist $eobChecklist)
    {
        $eobChecklist->update([
            'signed_off'    => false,
            'signed_off_by' => null,
            'signed_off_at' => null,
        ]);
        $eobChecklist->refreshItems();

        return back()->with('success', 'Checklist reset.');
    }
}
