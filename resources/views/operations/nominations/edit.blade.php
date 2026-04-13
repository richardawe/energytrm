<x-app-layout>
    <x-slot name="title">Edit {{ $nomination->nomination_number }}</x-slot>
    <div class="mb-3">
        <a href="{{ route('operations.nominations.index') }}" class="text-muted small text-decoration-none">← Nominations</a>
    </div>
    <form method="POST" action="{{ route('operations.nominations.update', $nomination) }}">
        @csrf @method('PUT')
        <div class="card card-etrm" style="max-width:700px;">
            <div class="card-header">{{ $nomination->nomination_number }} — {{ $nomination->trade->deal_number }}</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Gas Day</label>
                        <input type="date" name="gas_day" class="form-control"
                               value="{{ old('gas_day', $nomination->gas_day->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Pipeline Operator</label>
                        <input type="text" name="pipeline_operator" class="form-control"
                               value="{{ old('pipeline_operator', $nomination->pipeline_operator) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Delivery Point</label>
                        <input type="text" name="delivery_point" class="form-control"
                               value="{{ old('delivery_point', $nomination->delivery_point) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Nominated Volume</label>
                        <input type="number" name="nominated_volume" step="0.0001" class="form-control"
                               value="{{ old('nominated_volume', $nomination->nominated_volume) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Confirmed Volume</label>
                        <input type="number" name="confirmed_volume" step="0.0001" class="form-control"
                               value="{{ old('confirmed_volume', $nomination->confirmed_volume) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">UOM</label>
                        <select name="uom_id" class="form-select">
                            @foreach($uoms as $u)
                                <option value="{{ $u->id }}" {{ old('uom_id', $nomination->uom_id) == $u->id ? 'selected' : '' }}>{{ $u->code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="nomination_status" class="form-select">
                            @foreach(['Pending','Confirmed','Matched','Unmatched'] as $s)
                                <option value="{{ $s }}" {{ old('nomination_status', $nomination->nomination_status) == $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Comments</label>
                        <textarea name="comments" class="form-control" rows="2">{{ old('comments', $nomination->comments) }}</textarea>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex gap-2">
                <button type="submit" class="btn btn-primary"
                        style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Save Changes</button>
                <a href="{{ route('operations.nominations.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </form>
</x-app-layout>
