<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Broker;
use App\Models\BrokerCommission;
use App\Models\Currency;
use Illuminate\Http\Request;

class BrokerCommissionController extends Controller
{
    private function validationRules(): array
    {
        return [
            'name'               => 'required|string|max:100',
            'commission_rate'    => 'required|numeric|min:0',
            'rate_unit'          => 'nullable|string|max:50',
            'currency_id'        => 'nullable|exists:currencies,id',
            'payment_frequency'  => 'required|in:Per Trade,Monthly,Quarterly',
            'min_fee'            => 'nullable|numeric|min:0',
            'max_fee'            => 'nullable|numeric|min:0',
            'index_group'        => 'nullable|string|max:100',
            'effective_date'     => 'nullable|date',
            'is_default'         => 'boolean',
        ];
    }

    public function create(Broker $broker)
    {
        $currencies = Currency::orderBy('code')->get();
        return view('master.broker-commissions.create', compact('broker', 'currencies'));
    }

    public function store(Request $request, Broker $broker)
    {
        $data = $request->validate($this->validationRules());
        $data['is_default'] = $request->boolean('is_default');
        $data['broker_id']  = $broker->id;

        BrokerCommission::create($data);

        return redirect()->route('master.brokers.show', $broker)
            ->with('success', 'Commission schedule added.');
    }

    public function edit(BrokerCommission $commission)
    {
        $broker     = $commission->broker;
        $currencies = Currency::orderBy('code')->get();
        return view('master.broker-commissions.edit', compact('commission', 'broker', 'currencies'));
    }

    public function update(Request $request, BrokerCommission $commission)
    {
        $data = $request->validate($this->validationRules());
        $data['is_default'] = $request->boolean('is_default');

        $commission->update($data);

        return redirect()->route('master.brokers.show', $commission->broker)
            ->with('success', 'Commission schedule updated.');
    }

    public function destroy(BrokerCommission $commission)
    {
        $broker = $commission->broker;
        $commission->delete();

        return redirect()->route('master.brokers.show', $broker)
            ->with('success', 'Commission schedule deleted.');
    }
}
