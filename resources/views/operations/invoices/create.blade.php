<x-app-layout>
    <x-slot name="title">New Invoice — {{ $trade->deal_number }}</x-slot>

    <div class="mb-3">
        <a href="{{ route('trades.show', $trade) }}" class="text-muted small text-decoration-none">← {{ $trade->deal_number }}</a>
    </div>

    <form method="POST" action="{{ route('operations.invoices.storeFromTrade', $trade) }}">
        @csrf
        <input type="hidden" name="trade_id" value="{{ $trade->id }}">

        <div class="row g-3">
            <div class="col-lg-7">
                <div class="card card-etrm mb-3">
                    <div class="card-header">Trade Summary</div>
                    <div class="card-body" style="font-size:.9rem;">
                        <div class="row g-2">
                            <div class="col-4 text-muted">Deal No</div><div class="col-8 fw-semibold">{{ $trade->deal_number }}</div>
                            <div class="col-4 text-muted">Counterparty</div><div class="col-8">{{ $trade->counterparty->short_name }}</div>
                            <div class="col-4 text-muted">Product</div><div class="col-8">{{ $trade->product->name }}</div>
                            <div class="col-4 text-muted">Quantity</div><div class="col-8">{{ number_format($trade->quantity, 2) }} {{ $trade->uom->code }}</div>
                            <div class="col-4 text-muted">Price</div>
                            <div class="col-8">
                                @if($trade->fixed_float === 'Fixed')
                                    {{ number_format($trade->fixed_price, 4) }} {{ $trade->currency->code }}
                                @else
                                    Float ({{ $trade->index?->index_name }}) {{ $trade->spread >= 0 ? '+' : '' }}{{ $trade->spread }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-etrm mb-3">
                    <div class="card-header">Invoice Details</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Invoice Date <span class="text-danger">*</span></label>
                                <input type="date" name="invoice_date" class="form-control @error('invoice_date') is-invalid @enderror"
                                       value="{{ old('invoice_date', date('Y-m-d')) }}">
                                @error('invoice_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Due Date</label>
                                <input type="date" name="due_date" class="form-control"
                                       value="{{ old('due_date') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Status</label>
                                <select name="invoice_status" class="form-select">
                                    <option value="Draft"  {{ old('invoice_status','Draft') == 'Draft'  ? 'selected' : '' }}>Draft</option>
                                    <option value="Issued" {{ old('invoice_status','Draft') == 'Issued' ? 'selected' : '' }}>Issued</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Invoice Amount <span class="text-danger">*</span></label>
                                <input type="number" name="invoice_amount" step="0.01" min="0"
                                       class="form-control @error('invoice_amount') is-invalid @enderror"
                                       value="{{ old('invoice_amount', number_format($amount, 2, '.', '')) }}">
                                <div class="form-text">Auto-calculated: Qty × Price = {{ number_format($amount, 2) }}</div>
                                @error('invoice_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Currency</label>
                                <select name="currency_id" class="form-select">
                                    @foreach($currencies as $c)
                                        <option value="{{ $c->id }}" {{ old('currency_id', $trade->currency_id) == $c->id ? 'selected' : '' }}>
                                            {{ $c->code }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Payment Terms</label>
                                <select name="payment_terms_id" class="form-select">
                                    <option value="">— none —</option>
                                    @foreach($paymentTerms as $pt)
                                        <option value="{{ $pt->id }}" {{ old('payment_terms_id', $trade->payment_terms_id) == $pt->id ? 'selected' : '' }}>
                                            {{ $pt->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Comments</label>
                                <textarea name="comments" class="form-control" rows="2">{{ old('comments') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-primary"
                    style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Create Invoice</button>
            <a href="{{ route('trades.show', $trade) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</x-app-layout>
