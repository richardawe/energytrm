<x-app-layout>
    <x-slot name="title">Financial Trades — Blotter</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 fw-semibold">Financial Trades — Blotter</h5>
        @can('create', App\Models\FinancialTrade::class)
        <div class="dropdown">
            <button class="btn btn-primary btn-sm dropdown-toggle"
                    style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);"
                    data-bs-toggle="dropdown">+ New Financial Trade</button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('financials.financial-trades.create', ['type' => 'swap']) }}">Swap</a></li>
                <li><a class="dropdown-item" href="{{ route('financials.financial-trades.create', ['type' => 'futures']) }}">Futures</a></li>
                <li><a class="dropdown-item" href="{{ route('financials.financial-trades.create', ['type' => 'options']) }}">Options</a></li>
            </ul>
        </div>
        @endcan
    </div>

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('financials.financial-trades.index') }}" class="filter-bar mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-auto">
                <select name="instrument_type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    @foreach(['swap','futures','options'] as $t)
                        <option value="{{ $t }}" {{ request('instrument_type') == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    @foreach(['Pending','Validated','Active','Open','Settled','Closed','Expired','Exercised'] as $s)
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
                <select name="counterparty_id" class="form-select form-select-sm">
                    <option value="">All Counterparties</option>
                    @foreach($counterparties as $cp)
                        <option value="{{ $cp->id }}" {{ request('counterparty_id') == $cp->id ? 'selected' : '' }}>{{ $cp->short_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-auto">
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-outline-secondary">Filter</button>
                <a href="{{ route('financials.financial-trades.index') }}" class="btn btn-sm btn-link text-muted">Clear</a>
            </div>
        </div>
    </form>

    <div class="card card-etrm">
        <div class="card-body p-0">
            <table class="table table-etrm table-hover mb-0" style="font-size:.85rem;">
                <thead>
                    <tr>
                        <th>Deal No</th>
                        <th>Type</th>
                        <th>Trade Date</th>
                        <th>Counterparty</th>
                        <th>Product</th>
                        <th class="text-center">B/S</th>
                        <th>Key Terms</th>
                        <th>CCY</th>
                        <th class="text-center">Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trades as $t)
                    <tr>
                        <td><a href="{{ route('financials.financial-trades.show', $t) }}" class="fw-semibold text-decoration-none">{{ $t->deal_number }}</a></td>
                        <td>
                            @php
                                $typeClass = match($t->instrument_type) {
                                    'swap'    => 'bg-info text-dark',
                                    'futures' => 'bg-warning text-dark',
                                    'options' => 'bg-purple text-white',
                                    default   => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $typeClass }}" style="{{ $t->instrument_type === 'options' ? 'background:#6f42c1!important' : '' }}">
                                {{ ucfirst($t->instrument_type) }}
                            </span>
                        </td>
                        <td>{{ $t->trade_date->format('d-M-Y') }}</td>
                        <td>{{ $t->counterparty->short_name }}</td>
                        <td>{{ $t->product->name }}</td>
                        <td class="text-center">
                            <span class="badge {{ $t->buy_sell === 'Buy' ? 'bg-success' : 'bg-danger' }}">{{ $t->buy_sell }}</span>
                        </td>
                        <td class="text-muted small">
                            @if($t->instrument_type === 'swap')
                                {{ ucfirst($t->swap_type) }} · {{ number_format($t->notional_quantity, 0) }} {{ $t->uom?->code }}
                                · Fixed {{ $t->fixed_rate }}
                            @elseif($t->instrument_type === 'futures')
                                {{ $t->contract_code }} · {{ $t->num_contracts }} contracts
                                · {{ $t->expiry_date?->format('M-Y') }}
                            @elseif($t->instrument_type === 'options')
                                {{ ucfirst($t->option_type) }} · K={{ $t->strike_price }}
                                · {{ $t->option_expiry_date?->format('d-M-Y') }}
                            @endif
                        </td>
                        <td>{{ $t->currency->code }}</td>
                        <td class="text-center">
                            @php
                                $badgeClass = match($t->trade_status) {
                                    'Pending'   => 'badge-pending',
                                    'Validated', 'Active', 'Open' => 'badge-authorized',
                                    'Settled', 'Exercised', 'Expired', 'Closed' => 'badge-settled',
                                    default     => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $t->trade_status }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('financials.financial-trades.show', $t) }}" class="btn btn-outline-secondary btn-xs py-0 px-2" style="font-size:.75rem;">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center text-muted py-4">No financial trades found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($trades->hasPages())
        <div class="card-footer py-2">{{ $trades->links() }}</div>
        @endif
    </div>
</x-app-layout>
