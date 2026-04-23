<x-app-layout><x-slot name="title">Edit Note — {{ $party->short_name }}</x-slot>
<div class="mb-3">
    <a href="{{ route('master.parties.show', $party) }}" class="text-muted small text-decoration-none">← {{ $party->short_name }}</a>
</div>
<div class="card card-etrm" style="max-width:640px;"><div class="card-header">Edit Note — {{ $party->long_name }} <span class="text-muted small">(v{{ $note->version }})</span></div><div class="card-body">
<form method="POST" action="{{ route('master.parties.notes.update', [$party, $note]) }}">@csrf @method('PUT')
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label fw-semibold">Note Type</label>
        <input type="text" name="note_type" class="form-control" value="{{ old('note_type', $note->note_type) }}" maxlength="100" placeholder="e.g. Credit, Compliance, General">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Note Date *</label>
        <input type="date" name="note_date" class="form-control @error('note_date') is-invalid @enderror" value="{{ old('note_date', $note->note_date->format('Y-m-d')) }}" required>
        @error('note_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold">Title *</label>
        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $note->title) }}" maxlength="200" required>
        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold">Body *</label>
        <textarea name="body" class="form-control @error('body') is-invalid @enderror" rows="6" required>{{ old('body', $note->body) }}</textarea>
        @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary" style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Update</button>
    <a href="{{ route('master.parties.show', $party) }}" class="btn btn-outline-secondary">Cancel</a>
</div>
</form>
</div></div></x-app-layout>
