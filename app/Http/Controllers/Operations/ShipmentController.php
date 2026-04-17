<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Party;
use App\Models\Shipment;
use App\Models\Trade;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Shipment::with(['trade.counterparty', 'trade.product', 'carrier'])
            ->latest('id');

        if ($request->filled('status')) {
            $query->where('delivery_status', $request->status);
        }
        if ($request->filled('trade_id')) {
            $query->where('trade_id', $request->trade_id);
        }

        $shipments = $query->paginate(25)->withQueryString();
        $trades    = Trade::whereIn('trade_status', ['Validated', 'Active', 'Settled'])
                          ->orderBy('deal_number')->get();

        return view('operations.shipments.index', compact('shipments', 'trades'));
    }

    public function create(Request $request)
    {
        $trade    = $request->filled('trade_id') ? Trade::findOrFail($request->trade_id) : null;
        $trades   = Trade::whereIn('trade_status', ['Validated', 'Active', 'Settled'])->orderBy('deal_number')->get();
        $carriers = Party::where('internal_external', 'External')->orderBy('short_name')->get();

        return view('operations.shipments.create', compact('trade', 'trades', 'carriers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'trade_id'         => 'required|exists:trades,id',
            'vessel_name'      => 'nullable|string|max:100',
            'carrier_id'       => 'nullable|exists:parties,id',
            'incoterm_code'    => 'nullable|string|max:10',
            'load_port'        => 'nullable|string|max:100',
            'discharge_port'   => 'nullable|string|max:100',
            'bl_date'          => 'nullable|date',
            'eta_load'         => 'nullable|date',
            'eta_discharge'    => 'nullable|date',
            'actual_load'      => 'nullable|date',
            'actual_discharge' => 'nullable|date',
            'qty_nominated'    => 'nullable|numeric|min:0',
            'qty_loaded'       => 'nullable|numeric|min:0',
            'qty_discharged'   => 'nullable|numeric|min:0',
            'delivery_status'  => 'required|in:Scheduled,In Transit,Delivered,Completed,Cancelled',
            'comments'         => 'nullable|string|max:1000',
        ]);

        $data['shipment_number'] = Shipment::nextShipmentNumber();
        $data['created_by']      = auth()->id();

        $shipment = Shipment::create($data);

        // Auto-advance trade to Active on first confirmed delivery
        $this->advanceTradeToActive($shipment->trade);

        return redirect()->route('operations.shipments.index')
            ->with('success', 'Shipment created.');
    }

    public function show(Shipment $shipment)
    {
        $shipment->load(['trade.counterparty', 'trade.product', 'trade.uom', 'carrier', 'createdBy']);
        return view('operations.shipments.show', compact('shipment'));
    }

    public function edit(Shipment $shipment)
    {
        $trades   = Trade::whereIn('trade_status', ['Validated', 'Active', 'Settled'])->orderBy('deal_number')->get();
        $carriers = Party::where('internal_external', 'External')->orderBy('short_name')->get();
        return view('operations.shipments.edit', compact('shipment', 'trades', 'carriers'));
    }

    public function update(Request $request, Shipment $shipment)
    {
        $data = $request->validate([
            'vessel_name'      => 'nullable|string|max:100',
            'carrier_id'       => 'nullable|exists:parties,id',
            'incoterm_code'    => 'nullable|string|max:10',
            'load_port'        => 'nullable|string|max:100',
            'discharge_port'   => 'nullable|string|max:100',
            'bl_date'          => 'nullable|date',
            'eta_load'         => 'nullable|date',
            'eta_discharge'    => 'nullable|date',
            'actual_load'      => 'nullable|date',
            'actual_discharge' => 'nullable|date',
            'qty_nominated'    => 'nullable|numeric|min:0',
            'qty_loaded'       => 'nullable|numeric|min:0',
            'qty_discharged'   => 'nullable|numeric|min:0',
            'delivery_status'  => 'required|in:Scheduled,In Transit,Delivered,Completed,Cancelled',
            'comments'         => 'nullable|string|max:1000',
        ]);

        $shipment->update($data);

        $this->advanceTradeToActive($shipment->trade);

        return redirect()->route('operations.shipments.show', $shipment)
            ->with('success', 'Shipment updated.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function advanceTradeToActive(Trade $trade): void
    {
        if ($trade->trade_status === 'Validated') {
            $trade->update(['trade_status' => 'Active']);
            \App\Models\AuditLog::record($trade, 'activated');
        }
    }
}
