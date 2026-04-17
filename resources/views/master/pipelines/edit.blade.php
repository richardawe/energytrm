<x-app-layout>
    <x-slot name="title">Edit Pipeline</x-slot>

    <div class="mb-3">
        <a href="{{ route('master.pipelines.show', $pipeline) }}" class="text-muted small text-decoration-none">← {{ $pipeline->code }}</a>
    </div>

    <div class="card card-etrm" style="max-width:600px;">
        <div class="card-header">Edit Pipeline — {{ $pipeline->code }}</div>
        <div class="card-body">
            <form method="POST" action="{{ route('master.pipelines.update', $pipeline) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                               value="{{ old('code', $pipeline->code) }}" maxlength="20">
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $pipeline->name) }}" maxlength="100">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Commodity Type <span class="text-danger">*</span></label>
                        <select name="commodity_type" class="form-select">
                            @foreach(['Oil','Gas','LNG','Power'] as $ct)
                            <option value="{{ $ct }}" {{ old('commodity_type', $pipeline->commodity_type) == $ct ? 'selected' : '' }}>{{ $ct }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Operator</label>
                        <input type="text" name="operator" class="form-control" value="{{ old('operator', $pipeline->operator) }}" maxlength="100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Country</label>
                        <input type="text" name="country" class="form-control" value="{{ old('country', $pipeline->country) }}" maxlength="50">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="Authorized" {{ $pipeline->status === 'Authorized' ? 'selected' : '' }}>Authorized</option>
                            <option value="Do Not Use" {{ $pipeline->status === 'Do Not Use' ? 'selected' : '' }}>Do Not Use</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"
                            style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Update Pipeline</button>
                    <a href="{{ route('master.pipelines.show', $pipeline) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
