<x-app-layout>
    <x-slot name="title">{{ $trade->deal_number }}</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('financials.financial-trades.index') }}" class="text-muted small text-decoration-none">← Financial Trades</a>
        <div class="d-flex gap-2">
            @if($trade->trade_status === 'Pending')
                @can('update', $trade)
                <a href="{{ route('financials.financial-trades.edit', $trade) }}" class="btn btn-sm btn-outline-secondary">Amend</a>
                @endcan
                @can('validate', $trade)
                <form method="POST" action="{{ route('financials.financial-trades.validate', $trade) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Validate this trade?')">Validate</button>
                </form>
                @endcan
            @elseif(in_array($trade->trade_status, ['Active', 'Open']))
                @can('settle', $trade)
                <a href="{{ route('financials.financial-trades.settlements.create', $trade) }}" class="btn btn-sm btn-outline-primary">+ Settlement</a>
                @endcan
                @can('revert', $trade)
                <form method="POST" action="{{ route('financials.financial-trades.revert', $trade) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-warning" onclick="return confirm('Revert to Pending?')">Revert to Pending</button>
                </form>
                @endcan
            @elseif($trade->trade_status === 'Validated')
                @can('revert', $trade)
                <form method="POST" action="{{ route('financials.financial-trades.revert', $trade) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-warning" onclick="return confirm('Revert to Pending?')">Revert to Pending</button>
                </form>
                @endcan
            @endif
        </div>
    </div>

    {{-- Header banner --}}
    <div class="card card-etrm mb-3">
        <div class="card-body py-2">
            <div class="row align-items-center g-3">
                <div class="col-auto">
                    <div class="small text-muted">Deal Number</div>
                    <div class="fw-bold fs-5">{{ $trade->deal_number }}</div>
                </div>
                <div class="col-auto border-start ps-3">
                    <div class="small text-muted">TXN No</div>
                    <div class="fw-semibold">{{ $trade->transaction_number }}</div>
                </div>
                <div class="col-auto border-start ps-3">
                    <div class="small text-muted">Instrument</div>
                    <div class="fw-semibold">{{ $trade->instrument_number }}</div>
                </div>
                <div class="col-auto border-start ps-3">
                    <div class="small text-muted">Version</div>
                    <div class="fw-semibold">v{{ $trade->version }}</div>
                </div>
                <div class="col-auto border-start ps-3">
                    <div class="small text-muted">Type</div>
                    @php
                        $typeStyle = match($trade->instrument_type) {
                            'swap'    => 'background:#0dcaf0;color:#000',
                            'futures' => 'background:#ffc107;color:#000',
                            'options' => 'background:#6f42c1;color:#fff',
                            default   => 'background:#6c757d;color:#fff',
                        };
                    @endphp
                    <span class="badge fs-6" style="{{ $typeStyle }}">{{ ucfirst($trade->instrument_type) }}</span>
                </div>
                <div class="col-auto border-start ps-3">
                    <div class="small text-muted">Status</div>
                    @php
                        $badgeClass = match($trade->trade_status) {
                            'Pending'   => 'badge-pending',
                            'Validated', 'Active', 'Open' => 'badge-authorized',
                            'Settled', 'Exercised', 'Expired', 'Closed' => 'badge-settled',
                            default     => 'bg-secondary',
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }} fs-6">{{ $trade->trade_status }}</span>
                </div>
                <div class="col-auto border-start ps-3">
                    <div class="small text-muted">Buy / Sell</div>
                    <span class="badge {{ $trade->buy_sell === 'Buy' ? 'bg-success' : 'bg-danger' }} fs-6">{{ $trade->buy_sell }}</span>
                </div>
                <div class="col-auto border-start ps-3">
                    <div class="small text-muted">Pay / Rec</div>
                    <div class="fw-semibold">{{ $trade->pay_rec }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Left --}}
        <div class="col-lg-8">

            {{-- Common Details --}}
            <div class="card card-etrm mb-3">
                <div class="card-header">Deal Details</div>
                <div class="card-body">
                    <div class="row g-2" style="font-size:.9rem;">
                        <div class="col-md-3 text-muted">Trade Date</div>
                        <div class="col-md-3 fw-semibold">{{ $trade->trade_date->format('d-M-Y') }}</div>
                        <div class="col-md-3 text-muted">Internal BU</div>
                        <div class="col-md-3">{{ $trade->internalBu->short_name }}</div>

                        <div class="col-md-3 text-muted">Counterparty</div>
                        <div class="col-md-3">{{ $trade->counterparty->short_name }}</div>
                        <div class="col-md-3 text-muted">Portfolio</div>
                        <div class="col-md-3">{{ $trade->portfolio->name }}</div>

                        <div class="col-md-3 text-muted">Product</div>
                        <div class="col-md-3">{{ $trade->product->name }}</div>
                        <div class="col-md-3 text-muted">Currency</div>
                        <div class="col-md-3">{{ $trade->currency->code }}</div>
                    </div>
                </div>
            </div>

            {{-- Instrument-specific --}}
            @if($trade->instrument_type === 'swap')
            <div class="card card-etrm mb-3">
                <div class="card-header">Swap Terms</div>
                <div class="card-body">
                    <div class="row g-2" style="font-size:.9rem;">
                        <div class="col-md-3 text-muted">Swap Type</div>
                        <div class="col-md-3 fw-semibold">{{ ucfirst($trade->swap_type) }}</div>
                        <div class="col-md-3 text-muted">Payment Frequency</div>
                        <div class="col-md-3">{{ $trade->payment_frequency }}</div>

                        <div class="col-md-3 text-muted">Notional Qty</div>
                        <div class="col-md-3 fw-semibold">{{ number_format($trade->notional_quantity, 2) }} {{ $trade->uom?->code }}</div>
                        <div class="col-md-3 text-muted">Period</div>
                        <div class="col-md-3">{{ $trade->start_date->format('d-M-Y') }} – {{ $trade->end_date->format('d-M-Y') }}</div>

                        <div class="col-md-3 text-muted">Fixed Rate</div>
                        <div class="col-md-3 fw-semibold">{{ $trade->fixed_rate }}</div>
                        <div class="col-md-3 text-muted">Float Index</div>
                        <div class="col-md-3">{{ $trade->floatIndex?->index_name ?? '—' }}</div>

                        @if($trade->swap_type === 'basis')
                        <div class="col-md-3 text-muted">Second Index</div>
                        <div class="col-md-3">{{ $trade->secondIndex?->index_name ?? '—' }}</div>
                        @endif

                        <div class="col-md-3 text-muted">Spread</div>
                        <div class="col-md-3">{{ $trade->spread >= 0 ? '+' : '' }}{{ $trade->spread }}</div>
                    </div>
                </div>
            </div>

            {{-- Swap Analytics --}}
            <div class="card card-etrm mb-3" style="border-left:3px solid var(--etrm-secondary);">
                <div class="card-header fw-semibold">Live Analytics (MTM)</div>
                <div class="card-body">
                    <div class="row g-3 text-center">
                        <div class="col-md-4">
                            <div class="text-muted small">Fixed Leg Value</div>
                            <div class="fw-bold fs-5">{{ number_format($trade->fixedLegValue(), 2) }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Float Leg Value</div>
                            <div class="fw-bold fs-5">{{ number_format($trade->floatLegValue(), 2) }}</div>
                        </div>
                        <div class="col-md-4">
                            @php $mtm = $trade->swapMtm(); @endphp
                            <div class="text-muted small">Swap MTM</div>
                            <div class="fw-bold fs-5 {{ $mtm >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $mtm >= 0 ? '+' : '' }}{{ number_format($mtm, 2) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @elseif($trade->instrument_type === 'futures')
            <div class="card card-etrm mb-3">
                <div class="card-header">Futures Terms</div>
                <div class="card-body">
                    <div class="row g-2" style="font-size:.9rem;">
                        <div class="col-md-3 text-muted">Exchange</div>
                        <div class="col-md-3 fw-semibold">{{ $trade->exchange }}</div>
                        <div class="col-md-3 text-muted">Contract Code</div>
                        <div class="col-md-3 fw-semibold">{{ $trade->contract_code }}</div>

                        <div class="col-md-3 text-muted">Expiry Date</div>
                        <div class="col-md-3">{{ $trade->expiry_date->format('d-M-Y') }}</div>
                        <div class="col-md-3 text-muted">Contracts</div>
                        <div class="col-md-3 fw-semibold">{{ number_format($trade->num_contracts) }}</div>

                        <div class="col-md-3 text-muted">Contract Size</div>
                        <div class="col-md-3">{{ number_format($trade->contract_size, 2) }}</div>
                        <div class="col-md-3 text-muted">Futures Price</div>
                        <div class="col-md-3 fw-semibold">{{ $trade->futures_price }}</div>

                        <div class="col-md-3 text-muted">Margin / Contract</div>
                        <div class="col-md-3">{{ $trade->margin_requirement ?? '—' }}</div>
                        <div class="col-md-3 text-muted">Price Index</div>
                        <div class="col-md-3">{{ $trade->futuresIndex?->index_name ?? '—' }}</div>
                    </div>
                </div>
            </div>

            {{-- Futures Analytics --}}
            <div class="card card-etrm mb-3" style="border-left:3px solid var(--etrm-secondary);">
                <div class="card-header fw-semibold">Live Analytics (P&amp;L)</div>
                <div class="card-body">
                    <div class="row g-3 text-center">
                        <div class="col-md-4">
                            <div class="text-muted small">Trade Price</div>
                            <div class="fw-bold fs-5">{{ number_format($trade->futures_price, 4) }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Current Price</div>
                            <div class="fw-bold fs-5">{{ number_format($trade->currentFuturesPrice(), 4) }}</div>
                        </div>
                        <div class="col-md-4">
                            @php $pnl = $trade->futuresUnrealisedPnl(); @endphp
                            <div class="text-muted small">Unrealised P&amp;L</div>
                            <div class="fw-bold fs-5 {{ $pnl >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $pnl >= 0 ? '+' : '' }}{{ number_format($pnl, 2) }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Total Margin Required</div>
                            <div class="fw-bold">{{ number_format($trade->totalMarginRequirement(), 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            @elseif($trade->instrument_type === 'options')
            <div class="card card-etrm mb-3">
                <div class="card-header">Options Terms</div>
                <div class="card-body">
                    <div class="row g-2" style="font-size:.9rem;">
                        <div class="col-md-3 text-muted">Option Type</div>
                        <div class="col-md-3 fw-semibold">{{ ucfirst($trade->option_type) }}</div>
                        <div class="col-md-3 text-muted">Exercise Style</div>
                        <div class="col-md-3">{{ $trade->exercise_style }}</div>

                        <div class="col-md-3 text-muted">Strike Price</div>
                        <div class="col-md-3 fw-semibold">{{ $trade->strike_price }}</div>
                        <div class="col-md-3 text-muted">Expiry Date</div>
                        <div class="col-md-3">{{ $trade->option_expiry_date->format('d-M-Y') }}</div>

                        <div class="col-md-3 text-muted">Premium</div>
                        <div class="col-md-3">{{ $trade->premium }}</div>
                        <div class="col-md-3 text-muted">Volatility (σ)</div>
                        <div class="col-md-3">{{ $trade->volatility ? number_format($trade->volatility * 100, 1) . '%' : '—' }}</div>

                        <div class="col-md-3 text-muted">Underlying Index</div>
                        <div class="col-md-3">{{ $trade->underlyingIndex?->index_name ?? '—' }}</div>
                    </div>
                </div>
            </div>

            {{-- Options Analytics (Black-Scholes) --}}
            <div class="card card-etrm mb-3" style="border-left:3px solid var(--etrm-secondary);">
                <div class="card-header fw-semibold">Black-Scholes Analytics</div>
                <div class="card-body">
                    <div class="row g-3 text-center">
                        <div class="col-md-3">
                            <div class="text-muted small">Spot Price (S)</div>
                            <div class="fw-bold fs-5">{{ number_format($trade->optionSpotPrice(), 4) }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">Intrinsic Value</div>
                            <div class="fw-bold fs-5">{{ number_format($trade->intrinsicValue(), 4) }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">Time Value</div>
                            <div class="fw-bold fs-5">{{ number_format($trade->timeValue(), 4) }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">Delta (Δ)</div>
                            <div class="fw-bold fs-5">{{ number_format($trade->blackScholesDelta(), 4) }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">Gamma (Γ)</div>
                            <div class="fw-bold">{{ number_format($trade->blackScholesGamma(), 6) }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">Vega (ν) per 1%</div>
                            <div class="fw-bold">{{ number_format($trade->blackScholesVega(), 4) }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">Theta (Θ) /day</div>
                            <div class="fw-bold">{{ number_format($trade->blackScholesTheta(), 4) }}</div>
                        </div>
                    </div>
                    <p class="text-muted small mt-3 mb-0">Risk-free rate fixed at 5% (training constant). Spot from latest index price.</p>
                </div>
            </div>
            @endif

            {{-- Settlements --}}
            @if($trade->settlements->isNotEmpty())
            <div class="card card-etrm mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Settlements</span>
                    @if(in_array($trade->trade_status, ['Active','Open']))
                    @can('settle', $trade)
                    <a href="{{ route('financials.financial-trades.settlements.create', $trade) }}" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:.8rem;">+ Settlement</a>
                    @endcan
                    @endif
                </div>
                <div class="card-body p-0">
                    <table class="table table-etrm mb-0" style="font-size:.85rem;">
                        <thead>
                            <tr>
                                <th>Ref</th>
                                <th>Type</th>
                                <th>Period</th>
                                <th class="text-end">Net Amount</th>
                                <th>Settle Date</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trade->settlements as $s)
                            <tr>
                                <td class="fw-semibold small">{{ $s->settlement_number }}</td>
                                <td>{{ ucfirst($s->settlement_type) }}</td>
                                <td class="small text-muted">
                                    {{ $s->period_start?->format('d-M-Y') }} – {{ $s->period_end?->format('d-M-Y') }}
                                </td>
                                <td class="text-end fw-semibold {{ $s->net_amount >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $s->net_amount >= 0 ? '+' : '' }}{{ number_format($s->net_amount, 2) }}
                                </td>
                                <td>{{ $s->settlement_date->format('d-M-Y') }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $s->settlement_status === 'Confirmed' ? 'badge-authorized' : 'badge-pending' }}">
                                        {{ $s->settlement_status }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>

        {{-- Right --}}
        <div class="col-lg-4">
            <div class="card card-etrm mb-3">
                <div class="card-header">Other</div>
                <div class="card-body" style="font-size:.9rem;">
                    <div class="row g-2">
                        <div class="col-5 text-muted">Broker</div>
                        <div class="col-7">{{ $trade->broker?->name ?? '—' }}</div>
                        <div class="col-5 text-muted">Agreement</div>
                        <div class="col-7">{{ $trade->agreement?->name ?? '—' }}</div>
                        @if($trade->comments)
                        <div class="col-12 mt-2">
                            <div class="text-muted mb-1">Comments</div>
                            <div class="border rounded p-2 bg-light small">{{ $trade->comments }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Hedged Physical Trade Panel --}}
            @if($trade->hedgesPhysicalTrade)
            @php $phys = $trade->hedgesPhysicalTrade; @endphp
            <div class="card card-etrm mb-3" style="border-left:3px solid #198754;">
                <div class="card-header fw-semibold">
                    Hedged Physical Trade
                </div>
                <div class="card-body" style="font-size:.9rem;">
                    <div class="row g-2">
                        <div class="col-5 text-muted">Deal Number</div>
                        <div class="col-7">
                            <a href="{{ route('trades.show', $phys) }}" class="fw-semibold">{{ $phys->deal_number }}</a>
                        </div>
                        <div class="col-5 text-muted">Status</div>
                        <div class="col-7">
                            <span class="badge {{ in_array($phys->trade_status, ['Active','Validated']) ? 'badge-authorized' : 'badge-pending' }}">
                                {{ $phys->trade_status }}
                            </span>
                        </div>
                        <div class="col-5 text-muted">Product</div>
                        <div class="col-7">{{ $phys->product->name }}</div>
                        <div class="col-5 text-muted">Quantity</div>
                        <div class="col-7">{{ number_format($phys->quantity, 2) }} {{ $phys->uom->code }}</div>
                        <div class="col-5 text-muted">Delivery</div>
                        <div class="col-7 small">{{ $phys->start_date->format('d-M-Y') }} – {{ $phys->end_date->format('d-M-Y') }}</div>
                        <div class="col-5 text-muted">Direction</div>
                        <div class="col-7">
                            <span class="badge {{ $phys->buy_sell === 'Buy' ? 'bg-success' : 'bg-danger' }}">{{ $phys->buy_sell }}</span>
                        </div>
                        <div class="col-12 mt-1">
                            <a href="{{ route('trades.show', $phys) }}"
                               class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:.8rem;">
                                View Physical Trade →
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="card card-etrm mb-3">
                <div class="card-header">Record Info</div>
                <div class="card-body" style="font-size:.85rem;">
                    <div class="row g-2">
                        <div class="col-5 text-muted">Created by</div>
                        <div class="col-7">{{ $trade->createdBy->name }}</div>
                        <div class="col-5 text-muted">Created at</div>
                        <div class="col-7">{{ $trade->created_at->format('d-M-Y H:i') }}</div>
                        @if($trade->validatedBy)
                        <div class="col-5 text-muted">Validated by</div>
                        <div class="col-7">{{ $trade->validatedBy->name }}</div>
                        <div class="col-5 text-muted">Validated at</div>
                        <div class="col-7">{{ $trade->validated_at->format('d-M-Y H:i') }}</div>
                        @endif
                        <div class="col-5 text-muted">Last updated</div>
                        <div class="col-7">{{ $trade->updated_at->format('d-M-Y H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Audit Trail --}}
    @if($trade->auditLogs->isNotEmpty())
    <div class="card card-etrm mt-2">
        <div class="card-header fw-semibold">Audit Trail</div>
        <div class="card-body p-0">
            <table class="table table-etrm mb-0" style="font-size:.825rem;">
                <thead>
                    <tr>
                        <th style="width:120px;">Action</th>
                        <th>User</th>
                        <th>Timestamp</th>
                        <th>Changes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($trade->auditLogs as $log)
                    <tr>
                        <td><span class="badge {{ $log->actionBadgeClass() }}">{{ ucfirst($log->action) }}</span></td>
                        <td>{{ $log->user?->name ?? '—' }}</td>
                        <td class="text-muted">{{ $log->created_at->format('d-M-Y H:i:s') }}</td>
                        <td>
                            @if($log->action === 'updated' && $log->old_values && $log->new_values)
                                @php
                                    $skip = ['updated_at','created_at'];
                                    $changed = collect($log->new_values)
                                        ->filter(fn($v,$k) => !in_array($k,$skip) && ($log->old_values[$k] ?? null) != $v)
                                        ->keys();
                                @endphp
                                @if($changed->isNotEmpty())
                                <span class="text-muted">{{ $changed->map(fn($k) => str_replace('_',' ',ucfirst($k)))->join(', ') }}</span>
                                @endif
                            @elseif($log->action === 'created')
                                <span class="text-muted">Trade captured</span>
                            @elseif($log->action === 'validated')
                                <span class="text-muted">Status → {{ $trade->trade_status }}</span>
                            @elseif($log->action === 'reverted')
                                <span class="text-muted">Status → Pending</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</x-app-layout>
