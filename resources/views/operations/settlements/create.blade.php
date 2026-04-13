<x-app-layout>
    <x-slot name="title">Record Payment — {{ $invoice->invoice_number }}</x-slot>

    <div class="mb-3">
        <a href="{{ route('operations.invoices.show', $invoice) }}" class="text-muted small text-decoration-none">← {{ $invoice->invoice_number }}</a>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card card-etrm mb-3">
                <div class="card-header">Invoice Summary</div>
                <div class="card-body" style="font-size:.9rem;">
                    <div class="row g-2">
                        <div class="col-5 text-muted">Invoice No</div><div class="col-7 fw-semibold">{{ $invoice->invoice_number }}</div>
                        <div class="col-5 text-muted">Counterparty</div><div class="col-7">{{ $invoice->counterparty->short_name }}</div>
                        <div class="col-5 text-muted">Invoice Amount</div><div class="col-7 fw-bold">{{ number_format($invoice->invoice_amount, 2) }} {{ $invoice->currency->code }}</div>
                        <div class="col-5 text-muted">Outstanding</div>
                        <div class="col-7 fw-semibold text-danger">{{ number_format($invoice->outstandingAmount(), 2) }} {{ $invoice->currency->code }}</div>
                    </div>
                </div>
            </div>

            <div class="card card-etrm">
                <div class="card-header">Payment Details</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('operations.settlements.store', $invoice) }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Payment Amount <span class="text-danger">*</span></label>
                                <input type="number" name="payment_amount" step="0.01" min="0.01"
                                       class="form-control @error('payment_amount') is-invalid @enderror"
                                       value="{{ old('payment_amount', number_format($invoice->outstandingAmount(), 2, '.', '')) }}">
                                @error('payment_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date" class="form-control @error('payment_date') is-invalid @enderror"
                                       value="{{ old('payment_date', date('Y-m-d')) }}">
                                @error('payment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">FX Rate <span class="text-danger">*</span></label>
                                <input type="number" name="fx_rate" step="0.000001" min="0.000001"
                                       class="form-control" value="{{ old('fx_rate', '1.000000') }}">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Bank Reference</label>
                                <input type="text" name="bank_ref" class="form-control"
                                       value="{{ old('bank_ref') }}" placeholder="Wire transfer reference">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Status</label>
                                <select name="settlement_status" class="form-select">
                                    <option value="Pending"   {{ old('settlement_status') == 'Pending'   ? 'selected' : '' }}>Pending</option>
                                    <option value="Confirmed" {{ old('settlement_status') == 'Confirmed' ? 'selected' : '' }}>Confirmed</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Comments</label>
                                <textarea name="comments" class="form-control" rows="2">{{ old('comments') }}</textarea>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-success fw-semibold">Record Payment</button>
                            <a href="{{ route('operations.invoices.show', $invoice) }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
