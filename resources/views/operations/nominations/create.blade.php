<x-app-layout>
    <x-slot name="title">New Nomination</x-slot>
    <div class="mb-3">
        <a href="{{ route('operations.nominations.index') }}" class="text-muted small text-decoration-none">← Nominations</a>
    </div>
    <form method="POST" action="{{ route('operations.nominations.store') }}">
        @csrf
        <div class="card card-etrm" style="max-width:700px;">
            <div class="card-header">New Nomination</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Trade <span class="text-danger">*</span></label>
                        <select name="trade_id" class="form-select @error('trade_id') is-invalid @enderror">
                            <option value="">— select trade —</option>
                            @foreach($trades as $t)
                                <option value="{{ $t->id }}" {{ old('trade_id', $trade?->id) == $t->id ? 'selected' : '' }}>
                                    {{ $t->deal_number }} — {{ $t->product->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('trade_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Gas Day <span class="text-danger">*</span></label>
                        <input type="date" name="gas_day" class="form-control @error('gas_day') is-invalid @enderror"
                               value="{{ old('gas_day', date('Y-m-d')) }}">
                        @error('gas_day')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Scheduling Window</label>
                        <select name="scheduling_window" class="form-select">
                            <option value="">— optional —</option>
                            @foreach(['Day-Ahead','Intraday','Real-Time','Within-Day'] as $sw)
                            <option value="{{ $sw }}" {{ old('scheduling_window') == $sw ? 'selected' : '' }}>{{ $sw }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Pipeline Operator</label>
                        <input type="text" name="pipeline_operator" class="form-control"
                               value="{{ old('pipeline_operator') }}" placeholder="e.g. National Grid">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Delivery Point</label>
                        <input type="text" name="delivery_point" class="form-control"
                               value="{{ old('delivery_point') }}" placeholder="e.g. NBP">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Nominated Volume <span class="text-danger">*</span></label>
                        <input type="number" name="nominated_volume" step="0.0001" min="0"
                               class="form-control @error('nominated_volume') is-invalid @enderror"
                               value="{{ old('nominated_volume') }}">
                        @error('nominated_volume')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Confirmed Volume</label>
                        <input type="number" name="confirmed_volume" step="0.0001" min="0"
                               class="form-control" value="{{ old('confirmed_volume') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Counterpart Nominated Volume</label>
                        <input type="number" name="counterpart_nominated_volume" step="0.0001" min="0"
                               class="form-control" value="{{ old('counterpart_nominated_volume') }}"
                               placeholder="From counterparty">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Imbalance Quantity</label>
                        <input type="number" name="imbalance_quantity" step="0.0001"
                               class="form-control" value="{{ old('imbalance_quantity') }}"
                               placeholder="Nominated − Delivered">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">UOM <span class="text-danger">*</span></label>
                        <select name="uom_id" class="form-select @error('uom_id') is-invalid @enderror">
                            <option value="">—</option>
                            @foreach($uoms as $u)
                                <option value="{{ $u->id }}" {{ old('uom_id') == $u->id ? 'selected' : '' }}>{{ $u->code }}</option>
                            @endforeach
                        </select>
                        @error('uom_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="nomination_status" class="form-select">
                            @foreach(['Pending','Confirmed','Matched','Unmatched'] as $s)
                                <option value="{{ $s }}" {{ old('nomination_status','Pending') == $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Comments</label>
                        <textarea name="comments" class="form-control" rows="2">{{ old('comments') }}</textarea>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex gap-2">
                <button type="submit" class="btn btn-primary"
                        style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Create Nomination</button>
                <a href="{{ route('operations.nominations.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </form>
</x-app-layout>
