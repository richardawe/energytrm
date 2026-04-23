<x-app-layout><x-slot name="title">Edit Grid Point — {{ $index->index_name }}</x-slot>
<div class="mb-3">
    <a href="{{ route('master.indices.show', $index) }}" class="text-muted small text-decoration-none">← {{ $index->index_name }}</a>
</div>
<div class="card card-etrm" style="max-width:580px;"><div class="card-header">Edit Grid Point — {{ $index->index_name }}</div><div class="card-body">
<form method="POST" action="{{ route('master.indices.grid-points.update', [$index, $gridPoint]) }}">@csrf @method('PUT')
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label fw-semibold">Price Date <span class="text-danger">*</span></label>
        <input type="date" name="price_date" class="form-control @error('price_date') is-invalid @enderror" value="{{ old('price_date', $gridPoint->price_date instanceof \Carbon\Carbon ? $gridPoint->price_date->format('Y-m-d') : $gridPoint->price_date) }}" required>
        @error('price_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Price <span class="text-danger">*</span></label>
        <input type="number" name="price" step="0.000001" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $gridPoint->price) }}" required>
        @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-8">
        <label class="form-label fw-semibold">Grid Point Label</label>
        <input type="text" name="grid_point_label" class="form-control @error('grid_point_label') is-invalid @enderror" value="{{ old('grid_point_label', $gridPoint->grid_point_label) }}" maxlength="100">
        @error('grid_point_label')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Priority Level</label>
        <input type="number" name="priority_level" class="form-control @error('priority_level') is-invalid @enderror" value="{{ old('priority_level', $gridPoint->priority_level) }}" min="1" max="8">
        @error('priority_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Instrument Category</label>
        <select name="instrument_category" class="form-select @error('instrument_category') is-invalid @enderror">
            <option value="">— None —</option>
            @foreach(['Swap','Forward-D','Forward-M','Futures-D','Futures-M'] as $cat)
            <option value="{{ $cat }}" {{ old('instrument_category', $gridPoint->instrument_category) == $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
        </select>
        @error('instrument_category')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Sensitivity</label>
        <select name="sensitivity" class="form-select @error('sensitivity') is-invalid @enderror">
            <option value="">— None —</option>
            @foreach(['effective','raw','no'] as $sens)
            <option value="{{ $sens }}" {{ old('sensitivity', $gridPoint->sensitivity) == $sens ? 'selected' : '' }}>{{ ucfirst($sens) }}</option>
            @endforeach
        </select>
        @error('sensitivity')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">Start Time</label>
        <input type="time" name="start_time" class="form-control @error('start_time') is-invalid @enderror" value="{{ old('start_time', $gridPoint->start_time) }}">
        @error('start_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label fw-semibold">End Time</label>
        <input type="time" name="end_time" class="form-control @error('end_time') is-invalid @enderror" value="{{ old('end_time', $gridPoint->end_time) }}">
        @error('end_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Delta Shift</label>
        <input type="number" name="delta_shift" step="0.000001" class="form-control @error('delta_shift') is-invalid @enderror" value="{{ old('delta_shift', $gridPoint->delta_shift) }}">
        @error('delta_shift')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary" style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Save Changes</button>
    <a href="{{ route('master.indices.show', $index) }}" class="btn btn-outline-secondary">Cancel</a>
    <form method="POST" action="{{ route('master.indices.grid-points.destroy', [$index, $gridPoint]) }}" class="ms-auto d-inline" onsubmit="return confirm('Delete?')">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-outline-danger">Delete</button>
    </form>
</div>
</form>
</div></div>
</x-app-layout>
