<x-app-layout>
    <x-slot name="title">New Governing Body</x-slot>

    <div class="mb-3">
        <a href="{{ route('master.governing-bodies.index') }}" class="text-muted small text-decoration-none">← Governing Bodies</a>
    </div>

    <div class="card card-etrm" style="max-width:540px;">
        <div class="card-header">New Governing Body</div>
        <div class="card-body">
            <form method="POST" action="{{ route('master.governing-bodies.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" maxlength="150" placeholder="CFTC">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Jurisdiction</label>
                        <input type="text" name="jurisdiction" class="form-control @error('jurisdiction') is-invalid @enderror"
                               value="{{ old('jurisdiction') }}" maxlength="100" placeholder="Federal">
                        @error('jurisdiction')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Country</label>
                        <input type="text" name="country" class="form-control @error('country') is-invalid @enderror"
                               value="{{ old('country') }}" maxlength="100" placeholder="United States">
                        @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                   value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"
                            style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Save Governing Body</button>
                    <a href="{{ route('master.governing-bodies.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
