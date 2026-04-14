<x-app-layout>
    <x-slot name="title">Portfolio Analysis</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('risk.dashboard') }}" class="text-muted small text-decoration-none">Risk &amp; Analytics</a>
            <span class="text-muted small"> / </span>
            <span class="small fw-semibold">Portfolio Analysis</span>
        </div>
        <span class="text-muted small">All Pending + Validated + Settled trades</span>
    </div>

    {{-- Summary banner --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card card-etrm text-center py-2">
                <div class="text-muted small">Total Trade Value</div>
                <div class="fw-bold fs-5">{{ number_format($totals['trade_value'], 0) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-etrm text-center py-2">
                <div class="text-muted small">Total MTM Value</div>
                <div class="fw-bold fs-5">{{ number_format($totals['mtm_value'], 0) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-etrm text-center py-2">
                <div class="text-muted small">Total Unrealised P&amp;L</div>
                <div class="fw-bold fs-5 {{ $totals['unrealised_pnl'] >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ ($totals['unrealised_pnl'] >= 0 ? '+' : '') . number_format($totals['unrealised_pnl'], 0) }}
                </div>
            </div>
        </div>
    </div>

    {{-- Net Position by Portfolio --}}
    @forelse($portfolioRows as $row)
    <div class="card card-etrm mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="fw-semibold">{{ $row['portfolio']?->name ?? 'Unknown Portfolio' }}</span>
            <span class="text-muted small">
                Trade Value: <strong>{{ number_format($row['trade_value'], 0) }}</strong>
                &nbsp;|&nbsp;
                MTM: <strong>{{ number_format($row['mtm_value'], 0) }}</strong>
                &nbsp;|&nbsp;
                Unrealised P&amp;L:
                <strong class="{{ $row['unrealised_pnl'] >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ ($row['unrealised_pnl'] >= 0 ? '+' : '') . number_format($row['unrealised_pnl'], 0) }}
                </strong>
            </span>
        </div>
        <div class="card-body p-0">
            <table class="table table-etrm mb-0" style="font-size:.85rem;">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>UOM</th>
                        <th class="text-end">Net Position</th>
                        <th class="text-center">Direction</th>
                        <th class="text-end">Trade Value</th>
                        <th class="text-end">MTM Value</th>
                        <th class="text-end">Unrealised P&amp;L</th>
                        <th class="text-end"># Trades</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($row['products'] as $pr)
                    <tr>
                        <td class="fw-semibold">{{ $pr['product']?->name }}</td>
                        <td>{{ $pr['uom']?->code }}</td>
                        <td class="text-end fw-semibold">{{ number_format(abs($pr['net_qty']), 2) }}</td>
                        <td class="text-center">
                            @if($pr['net_qty'] > 0)
                                <span class="badge bg-success">Long</span>
                            @elseif($pr['net_qty'] < 0)
                                <span class="badge bg-danger">Short</span>
                            @else
                                <span class="badge bg-secondary">Flat</span>
                            @endif
                        </td>
                        <td class="text-end">{{ number_format($pr['trade_value'], 2) }}</td>
                        <td class="text-end">{{ number_format($pr['mtm_value'], 2) }}</td>
                        <td class="text-end {{ $pr['unrealised_pnl'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ ($pr['unrealised_pnl'] >= 0 ? '+' : '') . number_format($pr['unrealised_pnl'], 2) }}
                        </td>
                        <td class="text-end text-muted">{{ $pr['trade_count'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @empty
    <div class="alert alert-info">No active trades found.</div>
    @endforelse

    {{-- Exposure by Currency --}}
    @if($byCurrency->isNotEmpty())
    <div class="card card-etrm mt-2">
        <div class="card-header fw-semibold">Exposure by Currency (Active Trades)</div>
        <div class="card-body p-0">
            <table class="table table-etrm mb-0" style="font-size:.85rem;">
                <thead>
                    <tr>
                        <th>Currency</th>
                        <th class="text-end">Long Value</th>
                        <th class="text-end">Short Value</th>
                        <th class="text-end">Net Value</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($byCurrency as $cRow)
                    <tr>
                        <td class="fw-semibold">{{ $cRow['currency']?->code }}</td>
                        <td class="text-end text-success">{{ number_format($cRow['long_value'], 2) }}</td>
                        <td class="text-end text-danger">{{ number_format($cRow['short_value'], 2) }}</td>
                        <td class="text-end fw-semibold {{ $cRow['net_value'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ ($cRow['net_value'] >= 0 ? '+' : '') . number_format($cRow['net_value'], 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="text-muted small mt-2">
        Net Position = sum(Buy qty) − sum(Sell qty). MTM uses latest index price for float trades.
        Unrealised P&amp;L = (Market − Trade Price) × Qty × direction (float trades only).
    </div>
</x-app-layout>
