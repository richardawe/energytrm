<?php
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Party;
use App\Models\SettlementInstruction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettlementInstructionController extends Controller
{
    public function index()
    {
        $instructions = SettlementInstruction::with('party')
            ->orderByDesc('id')
            ->paginate(25);
        return view('master.settlement-instructions.index', compact('instructions'));
    }

    public function create()
    {
        $parties = Party::orderBy('short_name')->get();
        $linked  = SettlementInstruction::orderBy('si_number')->get();
        return view('master.settlement-instructions.create', compact('parties', 'linked'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'party_id'       => 'nullable|exists:parties,id',
            'si_name'        => 'required|string|max:150',
            'settler'        => 'nullable|string|max:100',
            'status'         => 'required|in:Auth Pending,Authorized,Amendment Pending,Do Not Use',
            'advice'         => 'nullable|string|max:100',
            'payment_method' => 'nullable|string|max:100',
            'account_name'   => 'nullable|string|max:150',
            'description'    => 'nullable|string',
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
            'is_dvp'         => 'boolean',
            'link_settle_id' => 'nullable|exists:settlement_instructions,id',
        ]);

        $data['is_dvp']      = $request->boolean('is_dvp');
        $data['si_number']   = SettlementInstruction::nextSiNumber();
        $data['created_by']  = Auth::id();
        $data['version']     = 0;

        $si = SettlementInstruction::create($data);

        return redirect()->route('master.settlement-instructions.show', $si)->with('success', 'Settlement instruction created.');
    }

    public function show(SettlementInstruction $settlementInstruction)
    {
        $settlementInstruction->load(['party', 'linkedSettlement', 'author']);
        return view('master.settlement-instructions.show', compact('settlementInstruction'));
    }

    public function edit(SettlementInstruction $settlementInstruction)
    {
        $parties = Party::orderBy('short_name')->get();
        $linked  = SettlementInstruction::where('id', '!=', $settlementInstruction->id)->orderBy('si_number')->get();
        return view('master.settlement-instructions.edit', compact('settlementInstruction', 'parties', 'linked'));
    }

    public function update(Request $request, SettlementInstruction $settlementInstruction)
    {
        $data = $request->validate([
            'party_id'       => 'nullable|exists:parties,id',
            'si_name'        => 'required|string|max:150',
            'settler'        => 'nullable|string|max:100',
            'status'         => 'required|in:Auth Pending,Authorized,Amendment Pending,Do Not Use',
            'advice'         => 'nullable|string|max:100',
            'payment_method' => 'nullable|string|max:100',
            'account_name'   => 'nullable|string|max:150',
            'description'    => 'nullable|string',
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
            'is_dvp'         => 'boolean',
            'link_settle_id' => 'nullable|exists:settlement_instructions,id',
        ]);

        $data['is_dvp']   = $request->boolean('is_dvp');
        $data['version']  = $settlementInstruction->version + 1;
        $settlementInstruction->update($data);

        return redirect()->route('master.settlement-instructions.show', $settlementInstruction)->with('success', 'Settlement instruction updated (v'.$data['version'].').');
    }

    public function destroy(SettlementInstruction $settlementInstruction)
    {
        $settlementInstruction->delete();
        return redirect()->route('master.settlement-instructions.index')->with('success', 'Settlement instruction deleted.');
    }
}
