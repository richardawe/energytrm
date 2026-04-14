<x-app-layout>
    <x-slot name="title">VaR &amp; Stress Tests</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('risk.dashboard') }}" class="text-muted small text-decoration-none">Risk &amp; Analytics</a>
            <span class="text-muted small"> / </span>
            <span class="small fw-semibold">VaR &amp; Stress Tests</span>
        </div>
        <span class="text-muted small">Float-priced active trades only</span>
    </div>

    <div class="row g-3 mb-4">
        {{-- Portfolio summary --}}
        <div class="col-md-3">
            <div class="card card-etrm text-center py-3">
                <div class="text-muted small">Float Trades</div>
                <div class="fw-bold fs-5">{{ $summary['float_trade_count'] }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-etrm text-center py-3">
                <div class="text-muted small">Fixed Trades</div>
                <div class="fw-bold fs-5 text-muted">{{ $summary['fixed_trade_count'] }}</div>
                <div class="text-muted" style="font-size:.7rem;">no market risk</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-etrm text-center py-3">
                <div class="text-muted small">Historical Scenarios</div>
                <div class="fw-bold fs-5">{{ $scenarioCount }}</div>
                <div class="text-muted" style="font-size:.7rem;">price observations used</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-etrm text-center py-3">
                <div class="text-muted small">Min. Required</div>
                <div class="fw-bold fs-5 {{ $scenarioCount >= $minDataPoints ? 'text-success' : 'text-warning' }}">
                    {{ $minDataPoints }}
                </div>
                <div class="text-muted" style="font-size:.7rem;">scenarios for VaR</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        {{-- Historical VaR --}}
        <div class="col-lg-5">
            <div class="card card-etrm h-100">
                <div class="card-header fw-semibold">Historical VaR (1-Day)</div>
                <div class="card-body">
                    @if($var95 !== null)
                    <div class="row g-3">
                        <div class="col-6 text-center">
                            <div class="text-muted small mb-1">95% VaR</div>
                            <div class="fw-bold fs-4 text-danger">{{ number_format($var95, 2) }}</div>
                            <div class="text-muted small">1-in-20 day loss</div>
                        </div>
                        <div class="col-6 text-center">
                            <div class="text-muted small mb-1">99% VaR</div>
                            <div class="fw-bold fs-4 text-danger">{{ number_format($var99, 2) }}</div>
                            <div class="text-muted small">1-in-100 day loss</div>
                        </div>
                    </div>
                    <hr>
                    <p class="text-muted small mb-0">
                        Based on {{ $scenarioCount }} historical price scenarios.
                        VaR = maximum loss not exceeded at given confidence level over one trading day.
                    </p>
                    @elseif($summary['float_trade_count'] === 0)
                    <div class="text-muted">No float-priced active trades in portfolio.</div>
                    @else
                    <div class="alert alert-warning py-2 mb-0">
                        Insufficient price history — need at least {{ $minDataPoints }} data points per index
                        (currently {{ $scenarioCount }}).
                        Enter more prices in <a href="{{ route('financials.market-prices.index') }}">Market Prices</a>.
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Stress Tests --}}
        <div class="col-lg-7">
            <div class="card card-etrm h-100">
                <div class="card-header fw-semibold">Stress Test Scenarios</div>
                <div class="card-body p-0">
                    <table class="table table-etrm mb-0" style="font-size:.875rem;">
                        <thead>
                            <tr>
                                <th>Scenario</th>
                                <th class="text-center">Price Shock</th>
                                <th class="text-end">Portfolio P&amp;L Impact</th>
                                <th class="text-center">Assessment</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stressResults as $s)
                            <tr>
                                <td class="fw-semibold">{{ $s['scenario'] }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $s['shock_pct'] < 0 ? 'bg-danger' : 'bg-success' }}">
                                        {{ $s['shock_pct'] > 0 ? '+' : '' }}{{ $s['shock_pct'] }}%
                                    </span>
                                </td>
                                <td class="text-end fw-semibold {{ $s['pnl_impact'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ ($s['pnl_impact'] >= 0 ? '+' : '') . number_format($s['pnl_impact'], 2) }}
                                </td>
                                <td class="text-center">
                                    @php $abs = abs($s['pnl_impact']); @endphp
                                    @if($abs == 0)
                                        <span class="badge bg-secondary">Neutral</span>
                                    @elseif($s['pnl_impact'] < 0 && $abs > 100000)
                                        <span class="badge bg-danger">Material Loss</span>
                                    @elseif($s['pnl_impact'] < 0)
                                        <span class="badge bg-warning text-dark">Loss</span>
                                    @else
                                        <span class="badge bg-success">Gain</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($summary['float_trade_count'] === 0)
                <div class="card-footer text-muted small">Stress tests only apply to float-priced trades.</div>
                @endif
            </div>
        </div>
    </div>

    <div class="text-muted small">
        <strong>Methodology:</strong>
        Historical VaR uses actual daily price returns from index grid points.
        Stress tests apply instantaneous parallel shocks to all index prices.
        Fixed-price trades have no market risk and are excluded from both calculations.
        This is a training simulation — not a production-grade risk system.
    </div>
</x-app-layout>
