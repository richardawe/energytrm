<x-app-layout><x-slot name="title">Add Credit Rating — {{ $party->short_name }}</x-slot>
<div class="mb-3">
    <a href="{{ route('master.parties.show', $party) }}" class="text-muted small text-decoration-none">← {{ $party->short_name }}</a>
</div>
<div class="card card-etrm" style="max-width:560px;"><div class="card-header">Add Credit Rating — {{ $party->long_name }}</div><div class="card-body">
<form method="POST" action="{{ route('master.parties.credit-ratings.store', $party) }}">@csrf
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label fw-semibold">Source *</label>
        <input type="text" name="source" class="form-control @error('source') is-invalid @enderror" value="{{ old('source') }}" maxlength="100" placeholder="e.g. Internal Rating, Moody's, S&amp;P" required>
        @error('source')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Rating *</label>
        <input type="text" name="rating" class="form-control @error('rating') is-invalid @enderror" value="{{ old('rating') }}" maxlength="20" placeholder="e.g. BBB+, Baa1, A-" required>
        @error('rating')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Effective Date</label>
        <input type="date" name="effective_date" class="form-control" value="{{ old('effective_date') }}">
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold">Notes</label>
        <textarea name="notes" class="form-control" rows="4">{{ old('notes') }}</textarea>
    </div>
</div>
<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary" style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Save</button>
    <a href="{{ route('master.parties.show', $party) }}" class="btn btn-outline-secondary">Cancel</a>
</div>
</form>
</div></div></x-app-layout>
