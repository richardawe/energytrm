<x-app-layout><x-slot name="title">Edit Trading Location</x-slot>
<div class="mb-3">
    <a href="{{ route('master.trading-locations.index') }}" class="text-muted small text-decoration-none">← Trading Locations</a>
</div>
<div class="card card-etrm" style="max-width:520px;"><div class="card-header">Edit Trading Location — {{ $tradingLocation->name }}</div><div class="card-body">
<form method="POST" action="{{ route('master.trading-locations.update', $tradingLocation) }}">@csrf @method('PATCH')
<div class="row g-3">
    <div class="col-12">
        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $tradingLocation->name) }}" maxlength="150" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">City</label>
        <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" value="{{ old('city', $tradingLocation->city) }}" maxlength="100">
        @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Country</label>
        <input type="text" name="country" class="form-control @error('country') is-invalid @enderror" value="{{ old('country', $tradingLocation->country) }}" maxlength="100">
        @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold">Timezone</label>
        <input type="text" name="timezone" class="form-control @error('timezone') is-invalid @enderror" value="{{ old('timezone', $tradingLocation->timezone) }}" maxlength="50" placeholder="e.g. Europe/London">
        @error('timezone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $tradingLocation->is_active) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Active</label>
        </div>
    </div>
</div>
<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary" style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Save Changes</button>
    <a href="{{ route('master.trading-locations.index') }}" class="btn btn-outline-secondary">Cancel</a>
    <form method="POST" action="{{ route('master.trading-locations.destroy', $tradingLocation) }}" class="ms-auto d-inline" onsubmit="return confirm('Delete?')">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-outline-danger">Delete</button>
    </form>
</div>
</form>
</div></div>
</x-app-layout>
