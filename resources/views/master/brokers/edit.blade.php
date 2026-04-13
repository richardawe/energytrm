<x-app-layout><x-slot name="title">Edit Broker</x-slot>
<div class="mb-3"><a href="{{ route('master.brokers.show', $broker) }}" class="text-muted small text-decoration-none">← {{ $broker->name }}</a></div>
<div class="card card-etrm" style="max-width:560px;"><div class="card-header">Edit — {{ $broker->name }}</div><div class="card-body">
<form method="POST" action="{{ route('master.brokers.update', $broker) }}">@csrf @method('PUT')
<div class="row g-3">
    <div class="col-md-8"><label class="form-label fw-semibold">Broker Name *</label><input type="text" name="name" class="form-control" value="{{ old('name', $broker->name) }}" required></div>
    <div class="col-md-4"><label class="form-label fw-semibold">Short Name</label><input type="text" name="short_name" class="form-control" value="{{ old('short_name', $broker->short_name) }}" maxlength="20"></div>
    <div class="col-md-6"><label class="form-label fw-semibold">Type *</label><select name="broker_type" class="form-select" required>@foreach(['Voice','Electronic','Hybrid'] as $t)<option value="{{ $t }}" {{ old('broker_type',$broker->broker_type)==$t ? 'selected' : '' }}>{{ $t }}</option>@endforeach</select></div>
    <div class="col-md-6"><label class="form-label fw-semibold">Status *</label><select name="status" class="form-select" required>@foreach(['Active','Suspended','Do Not Use'] as $s)<option value="{{ $s }}" {{ old('status',$broker->status)==$s ? 'selected' : '' }}>{{ $s }}</option>@endforeach</select></div>
    <div class="col-12"><label class="form-label fw-semibold">LEI</label><input type="text" name="lei" class="form-control" value="{{ old('lei', $broker->lei) }}" maxlength="20"></div>
    <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_regulated" value="1" {{ old('is_regulated', $broker->is_regulated) ? 'checked' : '' }}><label class="form-check-label">Regulated Entity</label></div></div>
</div>
<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary" style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Update</button>
    <a href="{{ route('master.brokers.show', $broker) }}" class="btn btn-outline-secondary">Cancel</a>
    <form method="POST" action="{{ route('master.brokers.destroy', $broker) }}" class="ms-auto" onsubmit="return confirm('Delete broker?')">@csrf @method('DELETE')<button type="submit" class="btn btn-outline-danger btn-sm">Delete</button></form>
</div></form></div></div></x-app-layout>
