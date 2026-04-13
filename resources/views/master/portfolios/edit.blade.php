<x-app-layout><x-slot name="title">Edit Portfolio</x-slot>
<div class="mb-3"><a href="{{ route('master.portfolios.index') }}" class="text-muted small text-decoration-none">← Portfolios</a></div>
<div class="card card-etrm" style="max-width:480px;"><div class="card-header">Edit — {{ $portfolio->name }}</div><div class="card-body">
<form method="POST" action="{{ route('master.portfolios.update', $portfolio) }}">@csrf @method('PUT')
<div class="mb-3"><label class="form-label fw-semibold">Portfolio Name *</label><input type="text" name="name" class="form-control" value="{{ old('name', $portfolio->name) }}" required></div>
<div class="mb-3"><label class="form-label fw-semibold">Business Unit</label>
<select name="business_unit_id" class="form-select"><option value="">— None —</option>@foreach($businessUnits as $bu)<option value="{{ $bu->id }}" {{ old('business_unit_id', $portfolio->business_unit_id) == $bu->id ? 'selected' : '' }}>{{ $bu->short_name }} — {{ $bu->long_name }}</option>@endforeach</select></div>
<div class="mb-3"><label class="form-label fw-semibold">Status *</label>
<select name="status" class="form-select" required>@foreach(['Authorized','Auth Pending','Do Not Use'] as $s)<option value="{{ $s }}" {{ old('status', $portfolio->status) == $s ? 'selected' : '' }}>{{ $s }}</option>@endforeach</select></div>
<div class="mb-3 form-check"><input class="form-check-input" type="checkbox" name="is_restricted" value="1" {{ old('is_restricted', $portfolio->is_restricted) ? 'checked' : '' }}><label class="form-check-label">Restricted Portfolio</label></div>
<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary" style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Update</button>
    <a href="{{ route('master.portfolios.index') }}" class="btn btn-outline-secondary">Cancel</a>
    <form method="POST" action="{{ route('master.portfolios.destroy', $portfolio) }}" class="ms-auto" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button type="submit" class="btn btn-outline-danger btn-sm">Delete</button></form>
</div></form></div></div></x-app-layout>
