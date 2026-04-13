<?php
namespace App\Http\Controllers\Master;
use App\Http\Controllers\Controller;
use App\Models\Broker;
use App\Models\BrokerCommission;
use App\Models\Currency;
use Illuminate\Http\Request;

class BrokerController extends Controller
{
    public function index()  { return view('master.brokers.index', ['brokers' => Broker::withCount('commissions')->orderBy('name')->paginate(25)]); }
    public function create() { return view('master.brokers.create'); }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:150', 'short_name' => 'nullable|string|max:20', 'broker_type' => 'required|in:Voice,Electronic,Hybrid', 'status' => 'required|in:Active,Suspended,Do Not Use', 'lei' => 'nullable|string|size:20', 'is_regulated' => 'boolean']);
        $data['is_regulated'] = $request->boolean('is_regulated');
        Broker::create($data);
        return redirect()->route('master.brokers.index')->with('success', 'Broker created.');
    }

    public function show(Broker $broker)  { return view('master.brokers.show', ['broker' => $broker->load(['commissions.currency'])]); }
    public function edit(Broker $broker)  { return view('master.brokers.edit', compact('broker')); }

    public function update(Request $request, Broker $broker)
    {
        $data = $request->validate(['name' => 'required|string|max:150', 'short_name' => 'nullable|string|max:20', 'broker_type' => 'required|in:Voice,Electronic,Hybrid', 'status' => 'required|in:Active,Suspended,Do Not Use', 'lei' => 'nullable|string|size:20', 'is_regulated' => 'boolean']);
        $data['is_regulated'] = $request->boolean('is_regulated');
        $data['version'] = $broker->version + 1;
        $broker->update($data);
        return redirect()->route('master.brokers.show', $broker)->with('success', 'Broker updated.');
    }

    public function destroy(Broker $broker) { $broker->delete(); return redirect()->route('master.brokers.index')->with('success', 'Deleted.'); }
}
