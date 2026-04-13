<?php

namespace App\Http\Controllers\Financials;

use App\Http\Controllers\Controller;
use App\Models\IndexDefinition;
use App\Models\IndexGridPoint;
use Illuminate\Http\Request;

class MarketPricesController extends Controller
{
    public function index()
    {
        $indices = IndexDefinition::with('latestPrice', 'baseCurrency')
            ->where('status', 'Authorized')
            ->orderBy('index_name')
            ->get();

        return view('financials.market-prices.index', compact('indices'));
    }

    public function show(IndexDefinition $index)
    {
        $points = $index->gridPoints()
            ->with('enteredBy')
            ->orderByDesc('price_date')
            ->paginate(30);

        return view('financials.market-prices.show', compact('index', 'points'));
    }

    public function store(Request $request, IndexDefinition $index)
    {
        $data = $request->validate([
            'price_date' => 'required|date',
            'price'      => 'required|numeric|min:0',
        ]);

        $data['index_id']   = $index->id;
        $data['entered_by'] = auth()->id();

        IndexGridPoint::updateOrCreate(
            ['index_id' => $index->id, 'price_date' => $data['price_date']],
            ['price' => $data['price'], 'entered_by' => $data['entered_by']]
        );

        return back()->with('success', 'Price saved for ' . $data['price_date'] . '.');
    }

    public function destroy(IndexGridPoint $point)
    {
        $index = $point->index;
        $point->delete();
        return redirect()->route('financials.market-prices.show', $index)
            ->with('success', 'Price point deleted.');
    }
}
