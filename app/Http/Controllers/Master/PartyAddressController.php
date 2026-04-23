<?php
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Party;
use App\Models\PartyAddress;
use App\Models\User;
use Illuminate\Http\Request;

class PartyAddressController extends Controller
{
    public function create(Party $party)
    {
        $users = User::orderBy('name')->get();
        return view('master.party-addresses.create', compact('party', 'users'));
    }

    public function store(Request $request, Party $party)
    {
        $data = $request->validate([
            'address_type'    => 'required|in:Main,Backup,Registered,Billing',
            'is_default'      => 'boolean',
            'address_line1'   => 'required|string|max:255',
            'address_line2'   => 'nullable|string|max:255',
            'city'            => 'required|string|max:100',
            'state'           => 'nullable|string|max:100',
            'country'         => 'required|string|max:100',
            'phone'           => 'nullable|string|max:50',
            'description'     => 'nullable|string|max:255',
            'contact_user_id' => 'nullable|exists:users,id',
            'effective_date'  => 'nullable|date',
        ]);

        $data['is_default'] = $request->boolean('is_default');
        $data['party_id']   = $party->id;

        PartyAddress::create($data);

        return redirect()->route('master.parties.show', $party)->with('success', 'Address added.');
    }

    public function edit(Party $party, PartyAddress $address)
    {
        $users = User::orderBy('name')->get();
        return view('master.party-addresses.edit', compact('party', 'address', 'users'));
    }

    public function update(Request $request, Party $party, PartyAddress $address)
    {
        $data = $request->validate([
            'address_type'    => 'required|in:Main,Backup,Registered,Billing',
            'is_default'      => 'boolean',
            'address_line1'   => 'required|string|max:255',
            'address_line2'   => 'nullable|string|max:255',
            'city'            => 'required|string|max:100',
            'state'           => 'nullable|string|max:100',
            'country'         => 'required|string|max:100',
            'phone'           => 'nullable|string|max:50',
            'description'     => 'nullable|string|max:255',
            'contact_user_id' => 'nullable|exists:users,id',
            'effective_date'  => 'nullable|date',
        ]);

        $data['is_default'] = $request->boolean('is_default');
        $address->update($data);

        return redirect()->route('master.parties.show', $party)->with('success', 'Address updated.');
    }

    public function destroy(Party $party, PartyAddress $address)
    {
        $address->delete();
        return redirect()->route('master.parties.show', $party)->with('success', 'Address deleted.');
    }
}
