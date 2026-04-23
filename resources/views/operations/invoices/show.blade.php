<x-app-layout>
    <x-slot name="title">{{ $invoice->invoice_number }}</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('operations.invoices.index') }}" class="text-muted small text-decoration-none">← Invoices</a>
        <div class="d-flex gap-2">
            @if(!in_array($invoice->invoice_status, ['Paid','Cancelled']))
                <a href="{{ route('operations.invoices.edit', $invoice) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                <a href="{{ route('operations.settlements.create', $invoice) }}" class="btn btn-sm btn-success">+ Record Payment</a>
            @endif
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card card-etrm mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">{{ $invoice->invoice_number }}</span>
                    @php
                        $cls = match($invoice->invoice_status) {
                            'Draft'     => 'badge-pending',
                            'Issued'    => 'badge-validated',
                            'Paid'      => 'badge-authorized',
                            'Overdue'   => 'badge-do-not-use',
                            'Cancelled' => 'bg-secondary',
                        };
                    @endphp
                    <span class="badge {{ $cls }} fs-6">{{ $invoice->invoice_status }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-2" style="font-size:.9rem;">
                        <div class="col-md-3 text-muted">Trade</div>
                        <div class="col-md-3"><a href="{{ route('trades.show', $invoice->trade) }}">{{ $invoice->trade->deal_number }}</a></div>
                        <div class="col-md-3 text-muted">Counterparty</div>
                        <div class="col-md-3">{{ $invoice->counterparty->short_name }}</div>

                        <div class="col-md-3 text-muted">Product</div>
                        <div class="col-md-3">{{ $invoice->trade->product->name }}</div>
                        <div class="col-md-3 text-muted">Quantity</div>
                        <div class="col-md-3">{{ number_format($invoice->trade->quantity, 2) }} {{ $invoice->trade->uom->code }}</div>

                        <div class="col-md-3 text-muted">Invoice Date</div>
                        <div class="col-md-3">{{ $invoice->invoice_date->format('d-M-Y') }}</div>
                        <div class="col-md-3 text-muted">Due Date</div>
                        <div class="col-md-3">{{ $invoice->due_date?->format('d-M-Y') ?? '—' }}</div>

                        <div class="col-md-3 text-muted">Invoice Amount</div>
                        <div class="col-md-3 fw-bold fs-6">{{ number_format($invoice->invoice_amount, 2) }} {{ $invoice->currency->code }}</div>
                        <div class="col-md-3 text-muted">Outstanding</div>
                        <div class="col-md-3 fw-semibold {{ $invoice->outstandingAmount() > 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($invoice->outstandingAmount(), 2) }} {{ $invoice->currency->code }}
                        </div>

                        <div class="col-md-3 text-muted">Payment Terms</div>
                        <div class="col-md-3">{{ $invoice->paymentTerms?->name ?? '—' }}</div>

                        <div class="col-md-3 text-muted">Invoice Type</div>
                        <div class="col-md-3">{{ $invoice->invoice_type ?? 'Commodity' }}</div>
                        @if($invoice->invoice_reference_external)
                        <div class="col-md-3 text-muted">External Reference</div>
                        <div class="col-md-3"><code>{{ $invoice->invoice_reference_external }}</code></div>
                        @endif
                        @if($invoice->tax_amount)
                        <div class="col-md-3 text-muted">Tax Amount</div>
                        <div class="col-md-3">{{ number_format($invoice->tax_amount, 2) }} {{ $invoice->currency->code }}
                            @if($invoice->tax_code) <span class="text-muted small">({{ $invoice->tax_code }})</span>@endif
                        </div>
                        @endif
                        <div class="col-md-3 text-muted">Dispute Status</div>
                        <div class="col-md-3">
                            @if($invoice->dispute_status === 'Disputed')
                                <span class="badge badge-do-not-use">Disputed</span>
                            @elseif($invoice->dispute_status === 'Under Review')
                                <span class="badge badge-pending">Under Review</span>
                            @else
                                <span class="badge bg-secondary">Undisputed</span>
                            @endif
                        </div>
                        @if($invoice->dispute_reason)
                        <div class="col-md-3 text-muted">Dispute Reason</div>
                        <div class="col-md-9 text-danger small">{{ $invoice->dispute_reason }}</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Settlements --}}
            <div class="card card-etrm">
                <div class="card-header">Payment History</div>
                @if($invoice->settlements->isEmpty())
                <div class="card-body text-muted small">No payments recorded yet.</div>
                @else
                <div class="card-body p-0">
                    <table class="table table-etrm mb-0" style="font-size:.85rem;">
                        <thead>
                            <tr>
                                <th>Settlement No</th>
                                <th>Payment Date</th>
                                <th class="text-end">Amount</th>
                                <th>FX Rate</th>
                                <th>Bank Ref</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->settlements as $s)
                            <tr>
                                <td>{{ $s->settlement_number }}</td>
                                <td>{{ $s->payment_date->format('d-M-Y') }}</td>
                                <td class="text-end fw-semibold">{{ number_format($s->payment_amount, 2) }}</td>
                                <td>{{ $s->fx_rate }}</td>
                                <td>{{ $s->bank_ref ?: '—' }}</td>
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
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-etrm">
                <div class="card-header">Audit</div>
                <div class="card-body" style="font-size:.85rem;">
                    <div class="row g-2">
                        <div class="col-5 text-muted">Created by</div>
                        <div class="col-7">{{ $invoice->createdBy->name }}</div>
                        <div class="col-5 text-muted">Created at</div>
                        <div class="col-7">{{ $invoice->created_at->format('d-M-Y H:i') }}</div>
                    </div>
                    @if($invoice->comments)
                    <hr>
                    <div class="text-muted small mb-1">Comments</div>
                    <div class="small">{{ $invoice->comments }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
