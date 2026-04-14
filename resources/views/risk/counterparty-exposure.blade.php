<x-app-layout>
    <x-slot name="title">Counterparty Exposure</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('risk.dashboard') }}" class="text-muted small text-decoration-none">Risk &amp; Analytics</a>
            <span class="text-muted small"> / </span>
            <span class="small fw-semibold">Counterparty Exposure</span>
        </div>
        <span class="text-muted small">Pending &amp; Validated trades only</span>
    </div>

    {{-- Breach summary --}}
    @if($breachCount > 0)
    <div class="alert alert-danger d-flex align-items-center py-2 mb-3" role="alert">
        <strong>&#9888; {{ $breachCount }} credit limit {{ $breachCount === 1 ? 'breach' : 'breaches' }} detected.</strong>
        &nbsp;Review the counterparties flagged below.
    </div>
    @else
    <div class="alert alert-success py-2 mb-3">All counterparty exposures within credit limits.</div>
    @endif

    <div class="card card-etrm">
        <div class="card-body p-0">
            <table class="table table-etrm table-hover mb-0" style="font-size:.875rem;">
                <thead>
                    <tr>
                        <th>Counterparty</th>
                        <th class="text-end">Active Trades</th>
                        <th class="text-end">Exposure</th>
                        <th class="text-end">Credit Limit</th>
                        <th class="text-end">Utilisation</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                    <tr class="{{ $row['breached'] ? 'table-danger' : '' }}">
                        <td>
                            <strong>{{ $row['party']?->short_name }}</strong>
                            @if($row['party']?->long_name)
                            <br><span class="text-muted" style="font-size:.75rem;">{{ $row['party']->long_name }}</span>
                            @endif
                        </td>
                        <td class="text-end">{{ $row['trade_count'] }}</td>
                        <td class="text-end fw-semibold">{{ number_format($row['exposure'], 2) }}</td>
                        <td class="text-end">
                            @if($row['credit_limit'])
                                {{ number_format($row['credit_limit'], 2) }}
                                <span class="text-muted small">{{ $row['party']?->creditLimitCurrency?->code }}</span>
                            @else
                                <span class="text-muted">Not set</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($row['utilisation'] !== null)
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <div style="width:80px;">
                                        <div class="progress" style="height:6px;">
                                            <div class="progress-bar {{ $row['breached'] ? 'bg-danger' : ($row['utilisation'] > 80 ? 'bg-warning' : 'bg-success') }}"
                                                 style="width:{{ min($row['utilisation'], 100) }}%"></div>
                                        </div>
                                    </div>
                                    <span class="{{ $row['breached'] ? 'text-danger fw-bold' : '' }}">
                                        {{ number_format($row['utilisation'], 1) }}%
                                    </span>
                                </div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($row['breached'])
                                <span class="badge bg-danger">BREACH</span>
                            @elseif($row['utilisation'] !== null && $row['utilisation'] > 80)
                                <span class="badge bg-warning text-dark">Near Limit</span>
                            @elseif($row['credit_limit'])
                                <span class="badge bg-success">OK</span>
                            @else
                                <span class="badge bg-secondary">No Limit</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No active trades found.</td></tr>
                    @endforelse
                </tbody>
                @if($rows->isNotEmpty())
                <tfoot>
                    <tr class="fw-bold" style="border-top:2px solid #dee2e6;">
                        <td>Total</td>
                        <td class="text-end">{{ $rows->sum('trade_count') }}</td>
                        <td class="text-end">{{ number_format($totalExposure, 2) }}</td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    <div class="text-muted small mt-2">
        Exposure = sum of trade values (Qty × Price) for all Pending and Validated trades per counterparty.
        Credit limits are set on the counterparty record in Master Data → Parties.
    </div>
</x-app-layout>
