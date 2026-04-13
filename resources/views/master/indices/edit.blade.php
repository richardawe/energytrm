<x-app-layout><x-slot name="title">Edit Index</x-slot>
<div class="mb-3"><a href="{{ route('master.indices.show', $index) }}" class="text-muted small text-decoration-none">← {{ $index->index_name }}</a></div>
<div class="card card-etrm" style="max-width:640px;"><div class="card-header">Edit Index — {{ $index->index_name }}</div><div class="card-body">
<form method="POST" action="{{ route('master.indices.update', $index) }}">@csrf @method('PUT')
<div class="row g-3">
    <div class="col-12"><label class="form-label fw-semibold">Index Name *</label><input type="text" name="index_name" class="form-control" value="{{ old('index_name', $index->index_name) }}" required></div>
    <div class="col-md-6"><label class="form-label fw-semibold">Market</label><input type="text" name="market" class="form-control" value="{{ old('market', $index->market) }}"></div>
    <div class="col-md-6"><label class="form-label fw-semibold">Index Group</label><input type="text" name="index_group" class="form-control" value="{{ old('index_group', $index->index_group) }}"></div>
    <div class="col-md-4"><label class="form-label fw-semibold">Format *</label><select name="format" class="form-select" required>@foreach(['Monthly','Daily','Quarterly','Annual'] as $f)<option value="{{ $f }}" {{ old('format',$index->format)==$f ? 'selected' : '' }}>{{ $f }}</option>@endforeach</select></div>
    <div class="col-md-4"><label class="form-label fw-semibold">Class</label><input type="text" name="class" class="form-control" value="{{ old('class', $index->class) }}"></div>
    <div class="col-md-4"><label class="form-label fw-semibold">Base Currency</label><select name="base_currency_id" class="form-select"><option value="">— None —</option>@foreach($currencies as $c)<option value="{{ $c->id }}" {{ old('base_currency_id',$index->base_currency_id)==$c->id ? 'selected' : '' }}>{{ $c->code }}</option>@endforeach</select></div>
    <div class="col-md-4"><label class="form-label fw-semibold">UOM</label><select name="uom_id" class="form-select"><option value="">— None —</option>@foreach($uoms as $u)<option value="{{ $u->id }}" {{ old('uom_id',$index->uom_id)==$u->id ? 'selected' : '' }}>{{ $u->code }}</option>@endforeach</select></div>
    <div class="col-md-4"><label class="form-label fw-semibold">Index Status *</label><select name="status" class="form-select" required>@foreach(['Custom','Official','Template'] as $s)<option value="{{ $s }}" {{ old('status',$index->status)==$s ? 'selected' : '' }}>{{ $s }}</option>@endforeach</select></div>
    <div class="col-md-4"><label class="form-label fw-semibold">Record Status *</label><select name="rec_status" class="form-select" required>@foreach(['Authorized','Auth Pending','Do Not Use'] as $s)<option value="{{ $s }}" {{ old('rec_status',$index->rec_status)==$s ? 'selected' : '' }}>{{ $s }}</option>@endforeach</select></div>
</div>
<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary" style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Update</button>
    <a href="{{ route('master.indices.show', $index) }}" class="btn btn-outline-secondary">Cancel</a>
    <form method="POST" action="{{ route('master.indices.destroy', $index) }}" class="ms-auto" onsubmit="return confirm('Delete this index and all its price data?')">@csrf @method('DELETE')<button type="submit" class="btn btn-outline-danger btn-sm">Delete</button></form>
</div></form></div></div></x-app-layout>
