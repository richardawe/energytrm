<x-app-layout>
    <x-slot name="title">Broker Fees</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('financials.dashboard') }}" class="text-muted small text-decoration-none">Financials</a>
            <span class="text-muted small"> / </span>
            <span class="small fw-semibold">Broker Fees</span>
        </div>
    </div>

    <form method="GET" class="filter-bar mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-auto">
                <select name="broker_id" class="form-select form-select-sm">
                    <option value="">All Brokers</option>
                    @foreach($brokers as $b)
                        <option value="{{ $b->id }}" {{ request('broker_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-secondary">Filter</button>
                <a href="{{ route('financials.broker-fees.index') }}" class="btn btn-sm btn-link text-muted">Clear</a>
            </div>
        </div>
    </form>

    <div class="card card-etrm mb-3">
        <div class="card-body p-0">
            <table class="table table-etrm table-hover mb-0" style="font-size:.85rem;">
                <thead>
                    <tr>
                        <th>Deal No</th>
                        <th>Trade Date</th>
                        <th>Counterparty</th>
                        <th>Product</th>
                        <th class="text-center">B/S</th>
                        <th class="text-end">Qty</th>
                        <th>UOM</th>
                        <th>Broker</th>
                        <th>Commission</th>
                        <th class="text-end">Fee</th>
                        <th>CCY</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                    @php $t = $row['trade']; $comm = $row['commission']; @endphp
                    <tr>
                        <td><a href="{{ route('trades.show', $t) }}" class="fw-semibold text-decoration-none">{{ $t->deal_number }}</a></td>
                        <td>{{ $t->trade_date->format('d-M-Y') }}</td>
                        <td>{{ $t->counterparty->short_name }}</td>
                        <td>{{ $t->product->name }}</td>
                        <td class="text-center">
                            <span class="badge {{ $t->buy_sell === 'Buy' ? 'bg-success' : 'bg-danger' }}">{{ $t->buy_sell }}</span>
                        </td>
                        <td class="text-end">{{ number_format($t->quantity, 0) }}</td>
                        <td>{{ $t->uom->code }}</td>
                        <td>{{ $t->broker->name }}</td>
                        <td class="text-muted small">
                            @if($comm)
                                {{ $comm->name }}
                                ({{ $comm->commission_rate }}
                                @if($comm->rate_unit === 'per_unit') /unit
                                @elseif($comm->rate_unit === 'percent') %
                                @else flat
                                @endif)
                            @else
                                <span class="text-muted">No schedule</span>
                            @endif
                        </td>
                        <td class="text-end fw-semibold">
                            {{ $row['fee'] !== null ? number_format($row['fee'], 2) : '—' }}
                        </td>
                        <td>{{ $comm?->currency?->code ?? '—' }}</td>
                        <td class="text-center">
                            <span class="badge {{ $t->trade_status === 'Settled' ? 'badge-authorized' : 'badge-validated' }}">
                                {{ $t->trade_status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="12" class="text-center text-muted py-4">No brokered trades found.</td></tr>
                    @endforelse
                </tbody>
                @if($rows->isNotEmpty())
                <tfoot>
                    <tr class="fw-bold" style="border-top:2px solid #dee2e6;">
                        <td colspan="9" class="text-end">Total Estimated Fees</td>
                        <td class="text-end">{{ number_format($totalFees, 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</x-app-layout>
