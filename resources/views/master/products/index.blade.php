<x-app-layout><x-slot name="title">Products</x-slot>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div><a href="{{ route('master.dashboard') }}" class="text-muted small text-decoration-none">Master Data</a> / <span class="small fw-semibold">Products</span></div>
    <a href="{{ route('master.products.create') }}" class="btn btn-primary btn-sm" style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">+ New</a>
</div>
<div class="card card-etrm"><div class="card-body p-0">
<table class="table table-etrm table-hover mb-0"><thead><tr><th>Product</th><th>Commodity Type</th><th>Default UOM</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($products as $p)
<tr><td>{{ $p->name }}</td><td>{{ $p->commodity_type ?? '—' }}</td><td>{{ $p->defaultUom?->code ?? '—' }}</td>
<td>@include('partials._status_badge', ['status' => $p->status])</td>
<td class="text-end"><a href="{{ route('master.products.edit', $p) }}" class="btn btn-outline-secondary btn-xs py-0 px-2" style="font-size:.75rem;">Edit</a></td></tr>
@empty<tr><td colspan="5" class="text-center text-muted py-3">No products yet.</td></tr>@endforelse
</tbody></table></div>
@if($products->hasPages())<div class="card-footer py-2">{{ $products->links() }}</div>@endif</div>
</x-app-layout>
