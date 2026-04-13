<x-app-layout><x-slot name="title">Edit Product</x-slot>
<div class="mb-3"><a href="{{ route('master.products.index') }}" class="text-muted small text-decoration-none">← Products</a></div>
<div class="card card-etrm" style="max-width:480px;"><div class="card-header">Edit — {{ $product->name }}</div><div class="card-body">
<form method="POST" action="{{ route('master.products.update', $product) }}">@csrf @method('PUT')
<div class="mb-3"><label class="form-label fw-semibold">Product Name *</label><input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}" required></div>
<div class="mb-3"><label class="form-label fw-semibold">Commodity Type</label>
<select name="commodity_type" class="form-select"><option value="">— Select —</option>@foreach(['Oil','Gas','LNG','Power','Coal','Metal','Agricultural'] as $t)<option value="{{ $t }}" {{ old('commodity_type', $product->commodity_type) == $t ? 'selected' : '' }}>{{ $t }}</option>@endforeach</select></div>
<div class="mb-3"><label class="form-label fw-semibold">Default UOM</label>
<select name="default_uom_id" class="form-select"><option value="">— None —</option>@foreach($uoms as $u)<option value="{{ $u->id }}" {{ old('default_uom_id', $product->default_uom_id) == $u->id ? 'selected' : '' }}>{{ $u->code }} — {{ $u->description }}</option>@endforeach</select></div>
<div class="mb-3"><label class="form-label fw-semibold">Status *</label>
<select name="status" class="form-select" required>@foreach(['Authorized','Auth Pending','Do Not Use'] as $s)<option value="{{ $s }}" {{ old('status', $product->status) == $s ? 'selected' : '' }}>{{ $s }}</option>@endforeach</select></div>
<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary" style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Update</button>
    <a href="{{ route('master.products.index') }}" class="btn btn-outline-secondary">Cancel</a>
    <form method="POST" action="{{ route('master.products.destroy', $product) }}" class="ms-auto" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button type="submit" class="btn btn-outline-danger btn-sm">Delete</button></form>
</div></form></div></div></x-app-layout>
