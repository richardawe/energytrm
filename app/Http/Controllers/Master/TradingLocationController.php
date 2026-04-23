<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\TradingLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TradingLocationController extends Controller
{
    public function index(): View
    {
        $tradingLocations = TradingLocation::orderBy('name')->paginate(25);

        return view('master.trading-locations.index', compact('tradingLocations'));
    }

    public function create(): View
    {
        return view('master.trading-locations.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:150'],
            'city'      => ['nullable', 'string', 'max:100'],
            'country'   => ['nullable', 'string', 'max:100'],
            'timezone'  => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        TradingLocation::create($data);

        return redirect()->route('master.trading-locations.index')
            ->with('success', 'Trading location created.');
    }

    public function edit(TradingLocation $tradingLocation): View
    {
        return view('master.trading-locations.edit', compact('tradingLocation'));
    }

    public function update(Request $request, TradingLocation $tradingLocation): RedirectResponse
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:150'],
            'city'      => ['nullable', 'string', 'max:100'],
            'country'   => ['nullable', 'string', 'max:100'],
            'timezone'  => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $tradingLocation->update($data);

        return redirect()->route('master.trading-locations.index')
            ->with('success', 'Trading location updated.');
    }

    public function destroy(TradingLocation $tradingLocation): RedirectResponse
    {
        $tradingLocation->delete();

        return redirect()->route('master.trading-locations.index')
            ->with('success', 'Trading location deleted.');
    }
}
