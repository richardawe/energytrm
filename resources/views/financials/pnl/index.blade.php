<x-app-layout>
    <x-slot name="title">P&L View</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('financials.dashboard') }}" class="text-muted small text-decoration-none">Financials</a>
            <span class="text-muted small"> / </span>
            <span class="small fw-semibold">P&amp;L View</span>
        </div>
        <span class="text-muted small">Read-only — calculated from live trade &amp; market data</span>
    </div>

    {{-- Summary banner --}}
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card card-etrm text-center py-2">
                <div class="text-muted small">Trade Value</div>
                <div class="fw-bold fs-5">{{ number_format($totals['trade_value'], 0) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-etrm text-center py-2">
                <div class="text-muted small">Market Value (MTM)</div>
                <div class="fw-bold fs-5">{{ number_format($totals['market_value'], 0) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-etrm text-center py-2">
                <div class="text-muted small">Unrealised P&amp;L</div>
                <div class="fw-bold fs-5 {{ $totals['unrealised_pnl'] >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ ($totals['unrealised_pnl'] >= 0 ? '+' : '') . number_format($totals['unrealised_pnl'], 0) }}
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-etrm text-center py-2">
                <div class="text-muted small">Realised P&amp;L</div>
                <div class="fw-bold fs-5 {{ $totals['realised_pnl'] >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ ($totals['realised_pnl'] >= 0 ? '+' : '') . number_format($totals['realised_pnl'], 0) }}
                </div>
            </div>
        </div>
    </div>

    <form method="GET" class="filter-bar mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-auto">
                <select name="product_id" class="form-select form-select-sm">
                    <option value="">All Products</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="currency_id" class="form-select form-select-sm">
                    <option value="">All Currencies</option>
                    @foreach($currencies as $c)
                        <option value="{{ $c->id }}" {{ request('currency_id') == $c->id ? 'selected' : '' }}>{{ $c->code }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-secondary">Filter</button>
                <a href="{{ route('financials.pnl.index') }}" class="btn btn-sm btn-link text-muted">Clear</a>
            </div>
        </div>
    </form>

    <div class="card card-etrm">
        <div class="card-body p-0">
            <table class="table table-etrm table-hover mb-0" style="font-size:.825rem;">
                <thead>
                    <tr>
                        <th>Deal No</th>
                        <th>Trade Date</th>
                        <th>Counterparty</th>
                        <th>Product</th>
                        <th class="text-center">B/S</th>
                        <th class="text-end">Qty</th>
                        <th class="text-end">Trade Price</th>
                        <th class="text-end">Market Price</th>
                        <th class="text-end">Trade Value</th>
                        <th class="text-end">Unrealised P&amp;L</th>
                        <th class="text-end">Realised P&amp;L</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                    @php $t = $row['trade']; @endphp
                    <tr>
                        <td><a href="{{ route('trades.show', $t) }}" class="fw-semibold text-decoration-none">{{ $t->deal_number }}</a></td>
                        <td>{{ $t->trade_date->format('d-M-Y') }}</td>
                        <td>{{ $t->counterparty->short_name }}</td>
                        <td>{{ $t->product->name }}</td>
                        <td class="text-center">
                            <span class="badge {{ $t->buy_sell === 'Buy' ? 'bg-success' : 'bg-danger' }}">{{ $t->buy_sell }}</span>
                        </td>
                        <td class="text-end">{{ number_format($t->quantity, 0) }} {{ $t->uom->code }}</td>
                        <td class="text-end">{{ number_format($row['tradePrice'], 4) }}</td>
                        <td class="text-end">
                            @if($t->fixed_float === 'Float')
                                <span title="{{ $t->index?->index_name }}">{{ number_format($row['marketPrice'], 4) }}</span>
                                <span class="text-muted" style="font-size:.7rem;">MTM</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end fw-semibold">{{ number_format($row['tradeValue'], 2) }}</td>
                        <td class="text-end fw-semibold {{ $row['unrealisedPnl'] >= 0 ? 'text-success' : 'text-danger' }}">
                            @if($t->fixed_float === 'Float')
                                {{ ($row['unrealisedPnl'] >= 0 ? '+' : '') . number_format($row['unrealisedPnl'], 2) }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end fw-semibold">
                            @if($row['realisedPnl'] !== null)
                                <span class="{{ $row['realisedPnl'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ ($row['realisedPnl'] >= 0 ? '+' : '') . number_format($row['realisedPnl'], 2) }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $t->trade_status === 'Settled' ? 'badge-authorized' : 'badge-validated' }}">
                                {{ $t->trade_status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="12" class="text-center text-muted py-4">No validated or settled trades found.</td></tr>
                    @endforelse
                </tbody>
                @if($rows->isNotEmpty())
                <tfoot>
                    <tr class="fw-bold" style="border-top:2px solid #dee2e6; background:#f8f9fa;">
                        <td colspan="8" class="text-end">Totals</td>
                        <td class="text-end">{{ number_format($totals['trade_value'], 2) }}</td>
                        <td class="text-end {{ $totals['unrealised_pnl'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ ($totals['unrealised_pnl'] >= 0 ? '+' : '') . number_format($totals['unrealised_pnl'], 2) }}
                        </td>
                        <td class="text-end {{ $totals['realised_pnl'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ ($totals['realised_pnl'] >= 0 ? '+' : '') . number_format($totals['realised_pnl'], 2) }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    <div class="text-muted small mt-2">
        Market Price = latest index grid point. Unrealised P&amp;L = (Market − Trade Price) × Qty × direction.
        Realised P&amp;L = confirmed settlement receipts vs invoice amount (Settled trades only).
    </div>
</x-app-layout>
