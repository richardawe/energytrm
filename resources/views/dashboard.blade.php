<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-0" style="color:var(--etrm-primary);">
                Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ Auth::user()->name }}
            </h5>
            <div class="text-muted small">{{ now()->format('l, d F Y') }} &mdash; {{ now()->format('H:i') }} UTC</div>
        </div>
        @if($pendingTotal > 0)
        <span class="badge bg-warning text-dark fs-6">
            {{ $pendingTotal }} item{{ $pendingTotal !== 1 ? 's' : '' }} pending your attention
        </span>
        @endif
    </div>

    {{-- ── KPI STRIP ──────────────────────────────────────────────────────── --}}
    <div class="row g-2 mb-3">

        {{-- Pending Validation --}}
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card card-etrm h-100" style="border-top:3px solid {{ $pendingTotal > 0 ? '#dc3545' : '#198754' }};">
                <div class="card-body p-3">
                    <div class="text-muted small mb-1">Pending Validation</div>
                    <div class="fw-bold fs-4 {{ $pendingTotal > 0 ? 'text-danger' : 'text-success' }}">
                        {{ $pendingTotal }}
                    </div>
                    <div class="text-muted" style="font-size:.75rem;">
                        {{ $pendingPhysical }}P / {{ $pendingFinancial }}F
                    </div>
                </div>
            </div>
        </div>

        {{-- Portfolio MTM --}}
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card card-etrm h-100" style="border-top:3px solid #0dcaf0;">
                <div class="card-body p-3">
                    <div class="text-muted small mb-1">Portfolio MTM</div>
                    <div class="fw-bold fs-5 {{ $portfolioMtm >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $portfolioMtm >= 0 ? '+' : '' }}{{ number_format($portfolioMtm / 1000, 1) }}K
                    </div>
                    <div class="text-muted" style="font-size:.75rem;">
                        Swap {{ number_format($swapMtm / 1000, 1) }}K · Fut {{ number_format($futuresPnl / 1000, 1) }}K
                    </div>
                </div>
            </div>
        </div>

        {{-- Physical P&L --}}
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card card-etrm h-100" style="border-top:3px solid #6f42c1;">
                <div class="card-body p-3">
                    <div class="text-muted small mb-1">Physical P&amp;L</div>
                    <div class="fw-bold fs-5 {{ $physicalPnl >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $physicalPnl >= 0 ? '+' : '' }}{{ number_format($physicalPnl / 1000, 1) }}K
                    </div>
                    <div class="text-muted" style="font-size:.75rem;">Float trades unrealised</div>
                </div>
            </div>
        </div>

        {{-- Credit Alerts --}}
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card card-etrm h-100" style="border-top:3px solid {{ $creditBreachCount > 0 ? '#dc3545' : '#198754' }};">
                <div class="card-body p-3">
                    <div class="text-muted small mb-1">Credit Breaches</div>
                    <div class="fw-bold fs-4 {{ $creditBreachCount > 0 ? 'text-danger' : 'text-success' }}">
                        {{ $creditBreachCount }}
                    </div>
                    <div class="text-muted" style="font-size:.75rem;">
                        <a href="{{ route('risk.counterparty-exposure') }}" class="text-muted text-decoration-none">View exposure →</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Settlements Overdue --}}
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card card-etrm h-100" style="border-top:3px solid {{ $settlementsOverdue > 0 ? '#fd7e14' : '#198754' }};">
                <div class="card-body p-3">
                    <div class="text-muted small mb-1">Settlements Overdue</div>
                    <div class="fw-bold fs-4 {{ $settlementsOverdue > 0 ? 'text-warning' : 'text-success' }}">
                        {{ $settlementsOverdue }}
                    </div>
                    <div class="text-muted" style="font-size:.75rem;">Past payment date</div>
                </div>
            </div>
        </div>

        {{-- EoB Checklist --}}
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card card-etrm h-100" style="border-top:3px solid #2e6da4;">
                <div class="card-body p-3">
                    <div class="text-muted small mb-1">EoB Checklist</div>
                    @if($eobToday)
                        <div class="fw-bold fs-4 {{ $eobComplete === 4 ? 'text-success' : 'text-warning' }}">
                            {{ $eobComplete }}/4
                        </div>
                        <div class="text-muted" style="font-size:.75rem;">
                            {{ $eobToday->signed_off ? 'Signed off ✓' : 'Not signed off' }}
                        </div>
                    @else
                        <div class="fw-bold fs-4 text-muted">—</div>
                        <div class="text-muted" style="font-size:.75rem;">
                            <a href="{{ route('eob.index') }}" class="text-muted text-decoration-none">Start checklist →</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

    {{-- ── MAIN BODY ───────────────────────────────────────────────────────── --}}
    <div class="row g-3">

        {{-- ── LEFT: Action Required + Recent Activity ── --}}
        <div class="col-lg-7">

            {{-- Action Required --}}
            <div class="card card-etrm mb-3">
                <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                    <span>Action Required</span>
                    @if($pendingTotal + $overdueInvoices->count() === 0)
                        <span class="badge bg-success">All clear</span>
                    @else
                        <span class="badge bg-danger">{{ $pendingTotal + $overdueInvoices->count() }} item{{ ($pendingTotal + $overdueInvoices->count()) !== 1 ? 's' : '' }}</span>
                    @endif
                </div>
                <div class="card-body p-0">

                    @if($pendingPhysicalTrades->isNotEmpty() || $pendingFinancialTrades->isNotEmpty())
                    <div class="px-3 pt-3 pb-1">
                        <div class="small fw-semibold text-muted text-uppercase mb-2" style="letter-spacing:.06em;font-size:.7rem;">
                            Trades Pending Validation
                        </div>
                        <table class="table table-sm mb-0" style="font-size:.84rem;">
                            <tbody>
                                @foreach($pendingPhysicalTrades as $t)
                                <tr>
                                    <td class="ps-0">
                                        <a href="{{ route('trades.show', $t) }}" class="fw-semibold text-decoration-none">{{ $t->deal_number }}</a>
                                    </td>
                                    <td><span class="badge bg-secondary" style="font-size:.7rem;">Physical</span></td>
                                    <td>{{ $t->counterparty?->short_name }}</td>
                                    <td>{{ $t->product?->name }}</td>
                                    <td class="text-muted">{{ $t->trade_date->format('d-M') }}</td>
                                    <td class="text-end pe-0">
                                        @can('validate', $t)
                                        <form method="POST" action="{{ route('trades.validate', $t) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-outline-success py-0 px-2" style="font-size:.75rem;">Validate</button>
                                        </form>
                                        @else
                                        <a href="{{ route('trades.show', $t) }}" class="btn btn-xs btn-outline-secondary py-0 px-2" style="font-size:.75rem;">View</a>
                                        @endcan
                                    </td>
                                </tr>
                                @endforeach
                                @foreach($pendingFinancialTrades as $t)
                                <tr>
                                    <td class="ps-0">
                                        <a href="{{ route('financials.financial-trades.show', $t) }}" class="fw-semibold text-decoration-none">{{ $t->deal_number }}</a>
                                    </td>
                                    <td>
                                        <span class="badge {{ $t->instrument_type === 'swap' ? 'bg-info text-dark' : ($t->instrument_type === 'futures' ? 'bg-warning text-dark' : '') }}" style="{{ $t->instrument_type === 'options' ? 'background:#6f42c1;color:#fff' : '' }} font-size:.7rem;">
                                            {{ ucfirst($t->instrument_type) }}
                                        </span>
                                    </td>
                                    <td>{{ $t->counterparty?->short_name }}</td>
                                    <td>{{ $t->product?->name }}</td>
                                    <td class="text-muted">{{ $t->trade_date->format('d-M') }}</td>
                                    <td class="text-end pe-0">
                                        @can('validate', $t)
                                        <form method="POST" action="{{ route('financials.financial-trades.validate', $t) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-outline-success py-0 px-2" style="font-size:.75rem;">Validate</button>
                                        </form>
                                        @else
                                        <a href="{{ route('financials.financial-trades.show', $t) }}" class="btn btn-xs btn-outline-secondary py-0 px-2" style="font-size:.75rem;">View</a>
                                        @endcan
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    @if($overdueInvoices->isNotEmpty())
                    <div class="px-3 pt-2 pb-1 {{ ($pendingPhysicalTrades->isNotEmpty() || $pendingFinancialTrades->isNotEmpty()) ? 'border-top' : '' }}">
                        <div class="small fw-semibold text-muted text-uppercase mb-2" style="letter-spacing:.06em;font-size:.7rem;">
                            Overdue Invoices
                        </div>
                        <table class="table table-sm mb-0" style="font-size:.84rem;">
                            <tbody>
                                @foreach($overdueInvoices as $inv)
                                <tr>
                                    <td class="ps-0 fw-semibold">{{ $inv->invoice_number }}</td>
                                    <td>{{ $inv->trade?->counterparty?->short_name }}</td>
                                    <td class="text-danger">Due {{ $inv->due_date->format('d-M-Y') }}</td>
                                    <td class="text-end pe-0 fw-semibold">{{ number_format($inv->invoice_amount, 0) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    @if($pendingPhysicalTrades->isEmpty() && $pendingFinancialTrades->isEmpty() && $overdueInvoices->isEmpty())
                    <div class="text-center text-muted py-4 small">
                        No actions required today.
                    </div>
                    @endif

                    @if($eobToday && !$eobToday->signed_off)
                    <div class="border-top px-3 py-2 d-flex justify-content-between align-items-center" style="background:#fff3cd22;">
                        <span class="small text-warning fw-semibold">EoB checklist not signed off for today</span>
                        <a href="{{ route('eob.index') }}" class="btn btn-xs btn-outline-warning py-0 px-2" style="font-size:.75rem;">Open checklist →</a>
                    </div>
                    @endif

                </div>
            </div>

            {{-- Recent Activity --}}
            <div class="card card-etrm">
                <div class="card-header fw-semibold d-flex justify-content-between">
                    <span>Recent Activity</span>
                    <a href="{{ route('admin.audit-log.index') }}" class="text-muted small text-decoration-none">Full log →</a>
                </div>
                <div class="card-body p-0">
                    @if($recentActivity->isNotEmpty())
                    <table class="table table-sm mb-0" style="font-size:.84rem;">
                        <tbody>
                            @foreach($recentActivity as $log)
                            <tr>
                                <td class="ps-3" style="width:90px;">
                                    <span class="badge {{ $log->actionBadgeClass() }}">{{ ucfirst($log->action) }}</span>
                                </td>
                                <td class="text-muted">{{ $log->user?->name ?? '—' }}</td>
                                <td>
                                    @php
                                        $modelName = class_basename($log->auditable_type ?? '');
                                        $label = match($modelName) {
                                            'Trade'          => 'Physical trade',
                                            'FinancialTrade' => 'Financial trade',
                                            'Invoice'        => 'Invoice',
                                            'Settlement'     => 'Settlement',
                                            default          => $modelName,
                                        };
                                    @endphp
                                    {{ $label }} #{{ $log->auditable_id }}
                                </td>
                                <td class="text-muted text-end pe-3" style="white-space:nowrap;font-size:.78rem;">
                                    {{ $log->created_at->diffForHumans() }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div class="text-center text-muted py-4 small">No activity recorded yet.</div>
                    @endif
                </div>
            </div>

        </div>

        {{-- ── RIGHT: Market Prices + Net Position + Training ── --}}
        <div class="col-lg-5">

            {{-- Market Prices --}}
            <div class="card card-etrm mb-3">
                <div class="card-header fw-semibold d-flex justify-content-between">
                    <span>Market Prices</span>
                    <a href="{{ route('financials.market-prices.index') }}" class="text-muted small text-decoration-none">Update →</a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0" style="font-size:.84rem;">
                        <thead>
                            <tr>
                                <th class="ps-3">Index</th>
                                <th class="text-end">Price</th>
                                <th class="text-end pe-3">Change</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($marketPrices as $mp)
                            <tr>
                                <td class="ps-3 fw-semibold">{{ $mp['name'] }}</td>
                                <td class="text-end">{{ number_format($mp['price'], 2) }}</td>
                                <td class="text-end pe-3 {{ $mp['change'] === null ? 'text-muted' : ($mp['change'] >= 0 ? 'text-success' : 'text-danger') }}">
                                    @if($mp['change'] !== null)
                                        {{ $mp['change'] >= 0 ? '+' : '' }}{{ number_format($mp['change'], 2) }}
                                        <span style="font-size:.75rem;">({{ $mp['change_pct'] >= 0 ? '+' : '' }}{{ number_format($mp['change_pct'], 1) }}%)</span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted py-3 small">No price data available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Net Position --}}
            <div class="card card-etrm mb-3">
                <div class="card-header fw-semibold d-flex justify-content-between">
                    <span>Net Position (Validated &amp; Active)</span>
                    <a href="{{ route('risk.portfolio-analysis') }}" class="text-muted small text-decoration-none">Full view →</a>
                </div>
                <div class="card-body">
                    @forelse($netPositions as $pos)
                    @php
                        $max = collect($netPositions)->max(fn($p) => max($p['long'], $p['short']));
                        $longPct  = $max > 0 ? $pos['long']  / $max * 100 : 0;
                        $shortPct = $max > 0 ? $pos['short'] / $max * 100 : 0;
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="fw-semibold">{{ $pos['product'] }}</span>
                            <span class="{{ $pos['net'] >= 0 ? 'text-success' : 'text-danger' }} fw-semibold">
                                Net: {{ $pos['net'] >= 0 ? '+' : '' }}{{ number_format($pos['net'], 0) }}
                            </span>
                        </div>
                        <div class="d-flex gap-1 align-items-center" style="font-size:.75rem;">
                            <span class="text-success" style="width:38px;">L {{ number_format($pos['long'] / 1000, 0) }}K</span>
                            <div class="flex-grow-1 position-relative" style="height:8px;background:#f0f0f0;border-radius:4px;overflow:hidden;">
                                <div style="position:absolute;left:0;top:0;height:100%;width:{{ $longPct }}%;background:#198754;border-radius:4px;opacity:.7;"></div>
                            </div>
                        </div>
                        <div class="d-flex gap-1 align-items-center mt-1" style="font-size:.75rem;">
                            <span class="text-danger" style="width:38px;">S {{ number_format($pos['short'] / 1000, 0) }}K</span>
                            <div class="flex-grow-1 position-relative" style="height:8px;background:#f0f0f0;border-radius:4px;overflow:hidden;">
                                <div style="position:absolute;left:0;top:0;height:100%;width:{{ $shortPct }}%;background:#dc3545;border-radius:4px;opacity:.7;"></div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted small py-2">No validated trades to display.</div>
                    @endforelse
                </div>
            </div>

            {{-- Training Progress --}}
            <div class="card card-etrm">
                <div class="card-header fw-semibold d-flex justify-content-between">
                    <span>Learning Path</span>
                    <a href="{{ route('training.scenarios.index') }}" class="text-muted small text-decoration-none">View all →</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" style="font-size:.84rem;">
                        @foreach($scenarios as $i => $s)
                        <a href="{{ route('training.scenarios.show', $s) }}"
                           class="list-group-item list-group-item-action py-2 px-3 d-flex align-items-center gap-2 text-decoration-none">
                            <span class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 text-white"
                                  style="width:22px;height:22px;font-size:.7rem;font-weight:700;background:var(--etrm-secondary);">
                                {{ $s->sort_order }}
                            </span>
                            <span class="flex-grow-1">{{ $s->title }}</span>
                            <span class="badge"
                                  style="font-size:.68rem;background:#e8f0e8;color:#1a6e3c;">
                                {{ ucfirst($s->module) }}
                            </span>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
