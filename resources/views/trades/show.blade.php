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
                        <div class="col-md-3 text-muted"></div>
                        <div class="col-md-3"></div>
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
                        @endif

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

            <div class="card card-etrm">
                <div class="card-header">Audit</div>
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
</x-app-layout>
