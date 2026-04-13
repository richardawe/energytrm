<x-app-layout>
    <x-slot name="title">Edit {{ $invoice->invoice_number }}</x-slot>

    <div class="mb-3">
        <a href="{{ route('operations.invoices.show', $invoice) }}" class="text-muted small text-decoration-none">← {{ $invoice->invoice_number }}</a>
    </div>

    <form method="POST" action="{{ route('operations.invoices.update', $invoice) }}">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-lg-7">
                <div class="card card-etrm mb-3">
                    <div class="card-header">Invoice — {{ $invoice->invoice_number }}</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Invoice Date</label>
                                <input type="date" name="invoice_date" class="form-control"
                                       value="{{ old('invoice_date', $invoice->invoice_date->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Due Date</label>
                                <input type="date" name="due_date" class="form-control"
                                       value="{{ old('due_date', $invoice->due_date?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Status</label>
                                <select name="invoice_status" class="form-select">
                                    @foreach(['Draft','Issued','Paid','Overdue','Cancelled'] as $s)
                                        <option value="{{ $s }}" {{ old('invoice_status', $invoice->invoice_status) == $s ? 'selected' : '' }}>{{ $s }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Invoice Amount</label>
                                <input type="number" name="invoice_amount" step="0.01" min="0" class="form-control"
                                       value="{{ old('invoice_amount', $invoice->invoice_amount) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Currency</label>
                                <select name="currency_id" class="form-select">
                                    @foreach($currencies as $c)
                                        <option value="{{ $c->id }}" {{ old('currency_id', $invoice->currency_id) == $c->id ? 'selected' : '' }}>{{ $c->code }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Payment Terms</label>
                                <select name="payment_terms_id" class="form-select">
                                    <option value="">— none —</option>
                                    @foreach($paymentTerms as $pt)
                                        <option value="{{ $pt->id }}" {{ old('payment_terms_id', $invoice->payment_terms_id) == $pt->id ? 'selected' : '' }}>{{ $pt->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Comments</label>
                                <textarea name="comments" class="form-control" rows="2">{{ old('comments', $invoice->comments) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-primary"
                    style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Save Changes</button>
            <a href="{{ route('operations.invoices.show', $invoice) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</x-app-layout>
