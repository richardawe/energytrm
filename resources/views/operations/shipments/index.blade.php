<x-app-layout>
    <x-slot name="title">Shipments</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('operations.dashboard') }}" class="text-muted small text-decoration-none">Operations</a>
            <span class="text-muted small"> / </span>
            <span class="small fw-semibold">Shipments</span>
        </div>
        <a href="{{ route('operations.shipments.create') }}" class="btn btn-primary btn-sm"
           style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">+ New Shipment</a>
    </div>

    <form method="GET" class="filter-bar mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    @foreach(['Scheduled','In Transit','Delivered','Completed','Cancelled'] as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="trade_id" class="form-select form-select-sm">
                    <option value="">All Trades</option>
                    @foreach($trades as $t)
                        <option value="{{ $t->id }}" {{ request('trade_id') == $t->id ? 'selected' : '' }}>{{ $t->deal_number }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-secondary">Filter</button>
                <a href="{{ route('operations.shipments.index') }}" class="btn btn-sm btn-link text-muted">Clear</a>
            </div>
        </div>
    </form>

    <div class="card card-etrm">
        <div class="card-body p-0">
            <table class="table table-etrm table-hover mb-0" style="font-size:.85rem;">
                <thead>
                    <tr>
                        <th>Shipment No</th>
                        <th>Trade</th>
                        <th>Counterparty</th>
                        <th>Vessel</th>
                        <th>Load Port</th>
                        <th>Discharge Port</th>
                        <th>BL Date</th>
                        <th>Qty Loaded</th>
                        <th class="text-center">Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shipments as $s)
                    <tr>
                        <td><a href="{{ route('operations.shipments.show', $s) }}" class="fw-semibold text-decoration-none">{{ $s->shipment_number }}</a></td>
                        <td><a href="{{ route('trades.show', $s->trade) }}" class="text-decoration-none">{{ $s->trade->deal_number }}</a></td>
                        <td>{{ $s->trade->counterparty->short_name }}</td>
                        <td>{{ $s->vessel_name ?: '—' }}</td>
                        <td>{{ $s->load_port ?: '—' }}</td>
                        <td>{{ $s->discharge_port ?: '—' }}</td>
                        <td>{{ $s->bl_date?->format('d-M-Y') ?? '—' }}</td>
                        <td>{{ $s->qty_loaded ? number_format($s->qty_loaded, 0) : '—' }}</td>
                        <td class="text-center">
                            @php
                                $cls = match($s->delivery_status) {
                                    'Scheduled'  => 'badge-pending',
                                    'In Transit' => 'badge-auth-pending',
                                    'Delivered'  => 'badge-validated',
                                    'Completed'  => 'badge-authorized',
                                    'Cancelled'  => 'badge-do-not-use',
                                };
                            @endphp
                            <span class="badge {{ $cls }}">{{ $s->delivery_status }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('operations.shipments.edit', $s) }}" class="btn btn-outline-secondary btn-xs py-0 px-2" style="font-size:.75rem;">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center text-muted py-4">No shipments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($shipments->hasPages())
        <div class="card-footer py-2">{{ $shipments->links() }}</div>
        @endif
    </div>
</x-app-layout>
