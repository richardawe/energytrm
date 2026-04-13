<x-app-layout>
    <x-slot name="title">{{ $shipment->shipment_number }}</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('operations.shipments.index') }}" class="text-muted small text-decoration-none">← Shipments</a>
        <a href="{{ route('operations.shipments.edit', $shipment) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card card-etrm mb-3">
                <div class="card-header d-flex justify-content-between">
                    <span>{{ $shipment->shipment_number }}</span>
                    @php
                        $cls = match($shipment->delivery_status) {
                            'Scheduled'  => 'badge-pending',
                            'In Transit' => 'badge-auth-pending',
                            'Delivered'  => 'badge-validated',
                            'Completed'  => 'badge-authorized',
                            'Cancelled'  => 'badge-do-not-use',
                        };
                    @endphp
                    <span class="badge {{ $cls }}">{{ $shipment->delivery_status }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-2" style="font-size:.9rem;">
                        <div class="col-md-3 text-muted">Trade</div>
                        <div class="col-md-3"><a href="{{ route('trades.show', $shipment->trade) }}">{{ $shipment->trade->deal_number }}</a></div>
                        <div class="col-md-3 text-muted">Counterparty</div>
                        <div class="col-md-3">{{ $shipment->trade->counterparty->short_name }}</div>

                        <div class="col-md-3 text-muted">Product</div>
                        <div class="col-md-3">{{ $shipment->trade->product->name }}</div>
                        <div class="col-md-3 text-muted">Vessel</div>
                        <div class="col-md-3">{{ $shipment->vessel_name ?: '—' }}</div>

                        <div class="col-md-3 text-muted">Carrier</div>
                        <div class="col-md-3">{{ $shipment->carrier?->short_name ?? '—' }}</div>
                        <div class="col-md-3 text-muted">Incoterm</div>
                        <div class="col-md-3">{{ $shipment->incoterm_code ?: '—' }}</div>

                        <div class="col-md-3 text-muted">Load Port</div>
                        <div class="col-md-3">{{ $shipment->load_port ?: '—' }}</div>
                        <div class="col-md-3 text-muted">Discharge Port</div>
                        <div class="col-md-3">{{ $shipment->discharge_port ?: '—' }}</div>

                        <div class="col-md-3 text-muted">BL Date</div>
                        <div class="col-md-3">{{ $shipment->bl_date?->format('d-M-Y') ?? '—' }}</div>
                        <div class="col-md-3 text-muted">ETA Load</div>
                        <div class="col-md-3">{{ $shipment->eta_load?->format('d-M-Y') ?? '—' }}</div>

                        <div class="col-md-3 text-muted">ETA Discharge</div>
                        <div class="col-md-3">{{ $shipment->eta_discharge?->format('d-M-Y') ?? '—' }}</div>
                        <div class="col-md-3 text-muted">Actual Load</div>
                        <div class="col-md-3">{{ $shipment->actual_load?->format('d-M-Y') ?? '—' }}</div>

                        <div class="col-md-3 text-muted">Actual Discharge</div>
                        <div class="col-md-3">{{ $shipment->actual_discharge?->format('d-M-Y') ?? '—' }}</div>
                    </div>
                </div>
            </div>

            <div class="card card-etrm mb-3">
                <div class="card-header">Quantities ({{ $shipment->trade->uom->code }})</div>
                <div class="card-body">
                    <div class="row g-2" style="font-size:.9rem;">
                        <div class="col-md-3 text-muted">Nominated</div>
                        <div class="col-md-3">{{ $shipment->qty_nominated ? number_format($shipment->qty_nominated, 2) : '—' }}</div>
                        <div class="col-md-3 text-muted">Loaded</div>
                        <div class="col-md-3">{{ $shipment->qty_loaded ? number_format($shipment->qty_loaded, 2) : '—' }}</div>
                        <div class="col-md-3 text-muted">Discharged</div>
                        <div class="col-md-3">{{ $shipment->qty_discharged ? number_format($shipment->qty_discharged, 2) : '—' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-etrm">
                <div class="card-header">Audit</div>
                <div class="card-body" style="font-size:.85rem;">
                    <div class="row g-2">
                        <div class="col-5 text-muted">Created by</div>
                        <div class="col-7">{{ $shipment->createdBy->name }}</div>
                        <div class="col-5 text-muted">Created at</div>
                        <div class="col-7">{{ $shipment->created_at->format('d-M-Y H:i') }}</div>
                    </div>
                    @if($shipment->comments)
                    <hr>
                    <div class="text-muted small mb-1">Comments</div>
                    <div class="small">{{ $shipment->comments }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
