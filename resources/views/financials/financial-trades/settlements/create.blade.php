<x-app-layout>
    <x-slot name="title">New Settlement — {{ $trade->deal_number }}</x-slot>

    <div class="mb-3">
        <a href="{{ route('financials.financial-trades.show', $trade) }}" class="text-muted small text-decoration-none">← {{ $trade->deal_number }}</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-7">

            <div class="card card-etrm mb-3">
                <div class="card-header">Trade Summary</div>
                <div class="card-body" style="font-size:.9rem;">
                    <div class="row g-2">
                        <div class="col-4 text-muted">Deal</div>
                        <div class="col-8 fw-semibold">{{ $trade->deal_number }} — {{ ucfirst($trade->instrument_type) }}</div>
                        <div class="col-4 text-muted">Counterparty</div>
                        <div class="col-8">{{ $trade->counterparty->short_name }}</div>
                        <div class="col-4 text-muted">Direction</div>
                        <div class="col-8">{{ $trade->buy_sell }} / {{ $trade->pay_rec }}</div>
                        <div class="col-4 text-muted">Currency</div>
                        <div class="col-8">{{ $trade->currency->code }}</div>
                        <div class="col-4 text-muted">Status</div>
                        <div class="col-8">{{ $trade->trade_status }}</div>
                    </div>
                </div>
            </div>

            <div class="card card-etrm">
                <div class="card-header fw-semibold">Record Settlement</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('financials.financial-trades.settlements.store', $trade) }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Settlement Type <span class="text-danger">*</span></label>
                            <select name="settlement_type" class="form-select @error('settlement_type') is-invalid @enderror">
                                @foreach(['periodic' => 'Periodic (regular payment)', 'final' => 'Final (closes trade)', 'margin' => 'Margin call', 'premium' => 'Option premium'] as $val => $label)
                                <option value="{{ $val }}" {{ old('settlement_type') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('settlement_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Period Start</label>
                                <input type="date" name="period_start" class="form-control @error('period_start') is-invalid @enderror"
                                       value="{{ old('period_start') }}">
                                @error('period_start')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Period End</label>
                                <input type="date" name="period_end" class="form-control @error('period_end') is-invalid @enderror"
                                       value="{{ old('period_end') }}">
                                @error('period_end')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        @if($trade->instrument_type === 'swap')
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Fixed Leg Amount</label>
                                <input type="number" name="fixed_leg_amount" step="0.01" class="form-control"
                                       value="{{ old('fixed_leg_amount') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Float Leg Amount</label>
                                <input type="number" name="float_leg_amount" step="0.01" class="form-control"
                                       value="{{ old('float_leg_amount') }}">
                            </div>
                        </div>
                        @endif

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Net Amount <span class="text-danger">*</span>
                                    <small class="text-muted fw-normal">(+ = BU receives)</small>
                                </label>
                                <input type="number" name="net_amount" step="0.01"
                                       class="form-control @error('net_amount') is-invalid @enderror"
                                       value="{{ old('net_amount') }}">
                                @error('net_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Settlement Date <span class="text-danger">*</span></label>
                                <input type="date" name="settlement_date"
                                       class="form-control @error('settlement_date') is-invalid @enderror"
                                       value="{{ old('settlement_date', date('Y-m-d')) }}">
                                @error('settlement_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                <select name="settlement_status" class="form-select">
                                    <option value="Pending"   {{ old('settlement_status','Pending') == 'Pending'   ? 'selected' : '' }}>Pending</option>
                                    <option value="Confirmed" {{ old('settlement_status') == 'Confirmed' ? 'selected' : '' }}>Confirmed</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">FX Rate <span class="text-danger">*</span></label>
                                <input type="number" name="fx_rate" step="0.000001" min="0.000001"
                                       class="form-control @error('fx_rate') is-invalid @enderror"
                                       value="{{ old('fx_rate', '1.000000') }}">
                                @error('fx_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Bank Reference</label>
                                <input type="text" name="bank_ref" class="form-control" value="{{ old('bank_ref') }}" maxlength="100">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Comments</label>
                            <textarea name="comments" class="form-control" rows="2">{{ old('comments') }}</textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"
                                    style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">
                                Record Settlement
                            </button>
                            <a href="{{ route('financials.financial-trades.show', $trade) }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
