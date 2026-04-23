<x-app-layout>
    <x-slot name="title">{{ $trade->deal_number }}</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('trades.index') }}" class="text-muted small text-decoration-none">← Trade Blotter</a>
        </div>
        <div class="d-flex gap-2">
            @if($trade->trade_status === 'Pending')
                <a href="{{ route('trades.edit', $trade) }}" class="btn btn-sm btn-outline-secondary">Amend</a>
                <form method="POST" action="{{ route('trades.validate', $trade) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success"
                            onclick="return confirm('Validate this trade?')">Validate</button>
                </form>
            @elseif($trade->trade_status === 'Validated')
                <a href="{{ route('operations.shipments.create', ['trade_id' => $trade->id]) }}"
                   class="btn btn-sm btn-outline-secondary">+ Shipment</a>
                <a href="{{ route('operations.invoices.createFromTrade', $trade) }}"
                   class="btn btn-sm btn-outline-primary">+ Invoice</a>
                <form method="POST" action="{{ route('trades.revert', $trade) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-warning"
                            onclick="return confirm('Revert to Pending?')">Revert to Pending</button>
                </form>
            @endif
        </div>
    </div>

    {{-- Header banner --}}
    <div class="card card-etrm mb-3">
        <div class="card-body py-2">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="small text-muted">Deal Number</div>
                    <div class="fw-bold fs-5">{{ $trade->deal_number }}</div>
                </div>
                <div class="col-auto border-start ps-3">
                    <div class="small text-muted">Transaction No</div>
                    <div class="fw-semibold">{{ $trade->transaction_number }}</div>
                </div>
                <div class="col-auto border-start ps-3">
                    <div class="small text-muted">Instrument No</div>
                    <div class="fw-semibold">{{ $trade->instrument_number }}</div>
                </div>
                <div class="col-auto border-start ps-3">
                    <div class="small text-muted">Version</div>
                    <div class="fw-semibold">v{{ $trade->version }}</div>
                </div>
                <div class="col-auto border-start ps-3">
                    <div class="small text-muted">Status</div>
                    @php
                        $badgeClass = match($trade->trade_status) {
                            'Pending'   => 'badge-pending',
                            'Validated' => 'badge-authorized',
                            'Settled'   => 'badge-settled',
                            default     => 'bg-secondary',
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }} fs-6">{{ $trade->trade_status }}</span>
                </div>
                <div class="col-auto border-start ps-3">
                    <div class="small text-muted">Buy / Sell</div>
                    <span class="badge {{ $trade->buy_sell === 'Buy' ? 'bg-success' : 'bg-danger' }} fs-6">
                        {{ $trade->buy_sell }}
                    </span>
                </div>
                <div class="col-auto border-start ps-3">
                    <div class="small text-muted">Pay / Receive</div>
                    <div class="fw-semibold">{{ $trade->pay_rec }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Left --}}
        <div class="col-lg-8">

            <div class="card card-etrm mb-3">
                <div class="card-header">Deal Details</div>
                <div class="card-body">
                    <div class="row g-2" style="font-size:.9rem;">
                        <div class="col-md-3 text-muted">Trade Date</div>
                        <div class="col-md-3 fw-semibold">{{ $trade->trade_date->format('d-M-Y') }}</div>
                        <div class="col-md-3 text-muted">Delivery Period</div>
                        <div class="col-md-3 fw-semibold">{{ $trade->start_date->format('d-M-Y') }} – {{ $trade->end_date->format('d-M-Y') }}</div>

                        <div class="col-md-3 text-muted">Internal BU</div>
                        <div class="col-md-3">{{ $trade->internalBu->short_name }}</div>
                        <div class="col-md-3 text-muted">Portfolio</div>
                        <div class="col-md-3">{{ $trade->portfolio->name }}</div>

                        <div class="col-md-3 text-muted">Counterparty</div>
                        <div class="col-md-3">{{ $trade->counterparty->short_name }}</div>
                        <div class="col-md-3 text-muted">Trader</div>
                        <div class="col-md-3">{{ $trade->trader?->name ?? $trade->createdBy->name }}</div>
                    </div>
                </div>
            </div>

            <div class="card card-etrm mb-3">
                <div class="card-header">Product &amp; Pricing</div>
                <div class="card-body">
                    <div class="row g-2" style="font-size:.9rem;">
                        <div class="col-md-3 text-muted">Product</div>
                        <div class="col-md-3 fw-semibold">{{ $trade->product->name }}</div>
                        <div class="col-md-3 text-muted">Volume Type</div>
                        <div class="col-md-3">{{ $trade->volume_type }}</div>

                        <div class="col-md-3 text-muted">Quantity</div>
                        <div class="col-md-3 fw-semibold">{{ number_format($trade->quantity, 2) }} {{ $trade->uom->code }}</div>
                        <div class="col-md-3 text-muted">Currency</div>
                        <div class="col-md-3">{{ $trade->currency->code }}</div>

                        <div class="col-md-3 text-muted">Pricing Type</div>
                        <div class="col-md-3">{{ $trade->fixed_float }}</div>
                        @if($trade->fixed_float === 'Fixed')
                        <div class="col-md-3 text-muted">Fixed Price</div>
                        <div class="col-md-3 fw-semibold">{{ number_format($trade->fixed_price, 4) }}</div>
                        @else
                        <div class="col-md-3 text-muted">Index</div>
                        <div class="col-md-3 fw-semibold">{{ $trade->index?->index_name ?? '—' }}</div>
                        <div class="col-md-3 text-muted">Spread</div>
                        <div class="col-md-3">{{ $trade->spread >= 0 ? '+' : '' }}{{ $trade->spread }}</div>
                        <div class="col-md-3 text-muted">Reference Source</div>
                        <div class="col-md-3">{{ $trade->reference_source ?: '—' }}</div>
                        @endif

                        <div class="col-md-3 text-muted">Price Unit</div>
                        <div class="col-md-3">{{ $trade->priceUnit?->code ?? $trade->uom->code }}</div>
                        <div class="col-md-3 text-muted">Put / Call</div>
                        <div class="col-md-3">{{ $trade->put_call ?: '—' }}</div>

                        <div class="col-md-3 text-muted">Payment Terms</div>
                        <div class="col-md-3">{{ $trade->paymentTerms?->name ?? '—' }}</div>
                        <div class="col-md-3"></div><div class="col-md-3"></div>
                    </div>
                </div>
            </div>

            <div class="card card-etrm mb-3">
                <div class="card-header">Logistics</div>
                <div class="card-body">
                    <div class="row g-2" style="font-size:.9rem;">
                        <div class="col-md-3 text-muted">Incoterm</div>
                        <div class="col-md-3">{{ $trade->incoterm_code ?: '—' }}</div>
                        <div class="col-md-3 text-muted">Load Port</div>
                        <div class="col-md-3">{{ $trade->load_port ?: '—' }}</div>
                        <div class="col-md-3 text-muted">Discharge Port</div>
                        <div class="col-md-3">{{ $trade->discharge_port ?: '—' }}</div>
                        @if($trade->pipeline)
                        <div class="col-12"><hr class="my-1"></div>
                        <div class="col-md-3 text-muted">Pipeline</div>
                        <div class="col-md-3">{{ $trade->pipeline->code }} — {{ $trade->pipeline->name }}</div>
                        <div class="col-md-3 text-muted">Zone</div>
                        <div class="col-md-3">{{ $trade->zone ? $trade->zone->zone_code . ' — ' . $trade->zone->zone_name : '—' }}</div>
                        <div class="col-md-3 text-muted">Location</div>
                        <div class="col-md-3">{{ $trade->location ? $trade->location->location_code . ' (' . $trade->location->location_type . ')' : '—' }}</div>
                        <div class="col-md-3 text-muted">Fuel %</div>
                        <div class="col-md-3">{{ $trade->fuel_percent ? number_format($trade->fuel_percent, 4) . '%' : '—' }}</div>
                        @endif
                    </div>
                </div>
            </div>
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

            {{-- Hedge Link Panel --}}
            @if($trade->hedgedBy)
            @php $hedge = $trade->hedgedBy; @endphp
            <div class="card card-etrm mb-3" style="border-left:3px solid #0dcaf0;">
                <div class="card-header fw-semibold">
                    Hedge — <span class="badge" style="background:#0dcaf0;color:#000;">{{ ucfirst($hedge->instrument_type) }}</span>
                </div>
                <div class="card-body" style="font-size:.9rem;">
                    <div class="row g-2">
                        <div class="col-5 text-muted">Deal Number</div>
                        <div class="col-7">
                            <a href="{{ route('financials.financial-trades.show', $hedge) }}" class="fw-semibold">
                                {{ $hedge->deal_number }}
                            </a>
                        </div>
                        <div class="col-5 text-muted">Status</div>
                        <div class="col-7">
                            <span class="badge {{ in_array($hedge->trade_status, ['Active','Open']) ? 'badge-authorized' : 'badge-pending' }}">
                                {{ $hedge->trade_status }}
                            </span>
                        </div>
                        @if($hedge->instrument_type === 'swap')
                        <div class="col-5 text-muted">Swap MTM</div>
                        <div class="col-7 fw-semibold {{ $hedge->swapMtm() >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $hedge->swapMtm() >= 0 ? '+' : '' }}{{ number_format($hedge->swapMtm(), 2) }}
                            {{ $hedge->currency->code }}
                        </div>
                        @elseif($hedge->instrument_type === 'futures')
                        <div class="col-5 text-muted">Unrealised P&L</div>
                        <div class="col-7 fw-semibold {{ $hedge->futuresUnrealisedPnl() >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $hedge->futuresUnrealisedPnl() >= 0 ? '+' : '' }}{{ number_format($hedge->futuresUnrealisedPnl(), 2) }}
                        </div>
                        @endif
                        <div class="col-12 mt-1">
                            <a href="{{ route('financials.financial-trades.show', $hedge) }}"
                               class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:.8rem;">
                                View Hedge →
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Pricing & Scheduling --}}
            @if($trade->start_time || $trade->deal_volume_type || $trade->reset_period || $trade->payment_period || $trade->pricing_formula || $trade->transfer_method_id)
            <div class="card card-etrm mb-3">
                <div class="card-header">Pricing &amp; Scheduling</div>
                <div class="card-body" style="font-size:.85rem;">
                    <div class="row g-2">
                        @if($trade->start_time)
                        <div class="col-5 text-muted">Start Time</div>
                        <div class="col-7">{{ $trade->start_time }}</div>
                        @endif
                        @if($trade->deal_volume_type)
                        <div class="col-5 text-muted">Deal Volume Type</div>
                        <div class="col-7">{{ $trade->deal_volume_type }}</div>
                        @endif
                        @if($trade->reset_period)
                        <div class="col-5 text-muted">Reset Period</div>
                        <div class="col-7">{{ $trade->reset_period }}</div>
                        @endif
                        @if($trade->payment_period)
                        <div class="col-5 text-muted">Payment Period</div>
                        <div class="col-7">{{ $trade->payment_period }}</div>
                        @endif
                        @if($trade->payment_date_offset !== null)
                        <div class="col-5 text-muted">Payment Date Offset</div>
                        <div class="col-7">{{ $trade->payment_date_offset }} days</div>
                        @endif
                        @if($trade->transferMethod)
                        <div class="col-5 text-muted">Transfer Method</div>
                        <div class="col-7">{{ $trade->transferMethod->name }}</div>
                        @endif
                        @if($trade->pricing_formula)
                        <div class="col-12 mt-1">
                            <div class="text-muted mb-1">Pricing Formula</div>
                            <div class="border rounded p-2 bg-light small font-monospace">{{ $trade->pricing_formula }}</div>
                        </div>
                        @endif
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
                        <td>
                            <span class="badge {{ $log->actionBadgeClass() }}">
                                {{ ucfirst($log->action) }}
                            </span>
                        </td>
                        <td>{{ $log->user?->name ?? '—' }}</td>
                        <td class="text-muted">{{ $log->created_at->format('d-M-Y H:i:s') }}</td>
                        <td>
                            @if($log->action === 'updated' && $log->old_values && $log->new_values)
                                @php
                                    $skip = ['updated_at','created_at','remember_token'];
                                    $changed = collect($log->new_values)
                                        ->filter(fn($v,$k) => !in_array($k,$skip)
                                            && ($log->old_values[$k] ?? null) != $v)
                                        ->keys();
                                @endphp
                                @if($changed->isNotEmpty())
                                <span class="text-muted">
                                    {{ $changed->map(fn($k) => str_replace('_',' ',ucfirst($k)))->join(', ') }}
                                </span>
                                @endif
                            @elseif($log->action === 'created')
                                <span class="text-muted">Trade captured</span>
                            @elseif($log->action === 'validated')
                                <span class="text-muted">Status → Validated</span>
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
