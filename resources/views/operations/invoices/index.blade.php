<x-app-layout>
    <x-slot name="title">Invoices</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('operations.dashboard') }}" class="text-muted small text-decoration-none">Operations</a>
            <span class="text-muted small"> / </span>
            <span class="small fw-semibold">Invoices</span>
        </div>
    </div>

    <form method="GET" class="filter-bar mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    @foreach(['Draft','Issued','Paid','Overdue','Cancelled'] as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-secondary">Filter</button>
                <a href="{{ route('operations.invoices.index') }}" class="btn btn-sm btn-link text-muted">Clear</a>
            </div>
        </div>
    </form>

    <div class="card card-etrm">
        <div class="card-body p-0">
            <table class="table table-etrm table-hover mb-0" style="font-size:.85rem;">
                <thead>
                    <tr>
                        <th>Invoice No</th>
                        <th>Trade</th>
                        <th>Counterparty</th>
                        <th>Invoice Date</th>
                        <th>Due Date</th>
                        <th class="text-end">Amount</th>
                        <th>CCY</th>
                        <th class="text-center">Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                    <tr>
                        <td><a href="{{ route('operations.invoices.show', $inv) }}" class="fw-semibold text-decoration-none">{{ $inv->invoice_number }}</a></td>
                        <td><a href="{{ route('trades.show', $inv->trade) }}" class="text-decoration-none">{{ $inv->trade->deal_number }}</a></td>
                        <td>{{ $inv->counterparty->short_name }}</td>
                        <td>{{ $inv->invoice_date->format('d-M-Y') }}</td>
                        <td>{{ $inv->due_date?->format('d-M-Y') ?? '—' }}</td>
                        <td class="text-end fw-semibold">{{ number_format($inv->invoice_amount, 2) }}</td>
                        <td>{{ $inv->currency->code }}</td>
                        <td class="text-center">
                            @php
                                $cls = match($inv->invoice_status) {
                                    'Draft'     => 'badge-pending',
                                    'Issued'    => 'badge-validated',
                                    'Paid'      => 'badge-authorized',
                                    'Overdue'   => 'badge-do-not-use',
                                    'Cancelled' => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $cls }}">{{ $inv->invoice_status }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('operations.invoices.show', $inv) }}" class="btn btn-outline-secondary btn-xs py-0 px-2" style="font-size:.75rem;">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">No invoices found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())
        <div class="card-footer py-2">{{ $invoices->links() }}</div>
        @endif
    </div>
</x-app-layout>
