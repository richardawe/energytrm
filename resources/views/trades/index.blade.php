<x-app-layout>
    <x-slot name="title">Trade Blotter</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 fw-semibold">Physical Trades — Blotter</h5>
        <a href="{{ route('trades.create') }}" class="btn btn-primary btn-sm"
           style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">+ New Trade</a>
    </div>

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('trades.index') }}" class="filter-bar mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    @foreach(['Pending','Validated','Settled'] as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="buy_sell" class="form-select form-select-sm">
                    <option value="">Buy / Sell</option>
                    <option value="Buy"  {{ request('buy_sell') == 'Buy'  ? 'selected' : '' }}>Buy</option>
                    <option value="Sell" {{ request('buy_sell') == 'Sell' ? 'selected' : '' }}>Sell</option>
                </select>
            </div>
            <div class="col-auto">
                <select name="product_id" class="form-select form-select-sm">
                    <option value="">All Products</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="counterparty_id" class="form-select form-select-sm">
                    <option value="">All Counterparties</option>
                    @foreach($counterparties as $cp)
                        <option value="{{ $cp->id }}" {{ request('counterparty_id') == $cp->id ? 'selected' : '' }}>{{ $cp->short_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <input type="date" name="date_from" class="form-control form-control-sm"
                       value="{{ request('date_from') }}" placeholder="From">
            </div>
            <div class="col-auto">
                <input type="date" name="date_to" class="form-control form-control-sm"
                       value="{{ request('date_to') }}" placeholder="To">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-outline-secondary">Filter</button>
                <a href="{{ route('trades.index') }}" class="btn btn-sm btn-link text-muted">Clear</a>
            </div>
        </div>
    </form>

    <div class="card card-etrm">
        <div class="card-body p-0">
            <table class="table table-etrm table-hover mb-0" style="font-size:.85rem;">
                <thead>
                    <tr>
                        <th>Deal No</th>
                        <th>TXN No</th>
                        <th>Trade Date</th>
                        <th>Counterparty</th>
                        <th>Product</th>
                        <th class="text-center">B/S</th>
                        <th class="text-end">Qty</th>
                        <th>UOM</th>
                        <th>Pricing</th>
                        <th>CCY</th>
                        <th>Start</th>
                        <th>End</th>
                        <th class="text-center">Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trades as $t)
                    <tr>
                        <td><a href="{{ route('trades.show', $t) }}" class="fw-semibold text-decoration-none">{{ $t->deal_number }}</a></td>
                        <td class="text-muted small">{{ $t->transaction_number }}</td>
                        <td>{{ $t->trade_date->format('d-M-Y') }}</td>
                        <td>{{ $t->counterparty->short_name }}</td>
                        <td>{{ $t->product->name }}</td>
                        <td class="text-center">
                            <span class="badge {{ $t->buy_sell === 'Buy' ? 'bg-success' : 'bg-danger' }}">
                                {{ $t->buy_sell }}
                            </span>
                        </td>
                        <td class="text-end">{{ number_format($t->quantity, 0) }}</td>
                        <td>{{ $t->uom->code }}</td>
                        <td>
                            @if($t->fixed_float === 'Fixed')
                                {{ number_format($t->fixed_price, 2) }}
                            @else
                                <span class="text-muted small">Float</span>
                                @if($t->index) <span class="text-muted small">({{ $t->index->index_name }})</span> @endif
                                @if($t->spread != 0) <span class="text-muted small">{{ $t->spread >= 0 ? '+' : '' }}{{ $t->spread }}</span> @endif
                            @endif
                        </td>
                        <td>{{ $t->currency->code }}</td>
                        <td>{{ $t->start_date->format('d-M-Y') }}</td>
                        <td>{{ $t->end_date->format('d-M-Y') }}</td>
                        <td class="text-center">
                            @php
                                $badgeClass = match($t->trade_status) {
                                    'Pending'   => 'badge-pending',
                                    'Validated' => 'badge-authorized',
                                    'Settled'   => 'badge-settled',
                                    default     => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $t->trade_status }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('trades.show', $t) }}" class="btn btn-outline-secondary btn-xs py-0 px-2" style="font-size:.75rem;">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="14" class="text-center text-muted py-4">No trades found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($trades->hasPages())
        <div class="card-footer py-2">{{ $trades->links() }}</div>
        @endif
    </div>
</x-app-layout>
