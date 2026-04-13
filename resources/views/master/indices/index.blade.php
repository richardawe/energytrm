<x-app-layout><x-slot name="title">Indices / Curves</x-slot>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div><a href="{{ route('master.dashboard') }}" class="text-muted small text-decoration-none">Master Data</a> / <span class="small fw-semibold">Indices / Curves</span></div>
    <a href="{{ route('master.indices.create') }}" class="btn btn-primary btn-sm" style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">+ New Index</a>
</div>
<div class="card card-etrm"><div class="card-body p-0">
<table class="table table-etrm table-hover mb-0"><thead><tr><th>Index Name</th><th>Market</th><th>Format</th><th>Currency</th><th>UOM</th><th>Latest Price</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($indices as $idx)
<tr>
    <td><a href="{{ route('master.indices.show', $idx) }}" class="text-decoration-none fw-semibold">{{ $idx->index_name }}</a></td>
    <td>{{ $idx->market ?? '—' }}</td><td>{{ $idx->format }}</td>
    <td>{{ $idx->baseCurrency?->code ?? '—' }}</td><td>{{ $idx->uom?->code ?? '—' }}</td>
    <td>@if($idx->latestPrice) <strong>{{ number_format($idx->latestPrice->price, 2) }}</strong> <span class="text-muted small">{{ $idx->latestPrice->price_date->format('d M') }}</span>@else —@endif</td>
    <td>@include('partials._status_badge', ['status' => $idx->rec_status])</td>
    <td class="text-end"><a href="{{ route('master.indices.show', $idx) }}" class="btn btn-outline-secondary btn-xs py-0 px-2" style="font-size:.75rem;">View</a></td>
</tr>
@empty<tr><td colspan="8" class="text-center text-muted py-3">No indices defined yet.</td></tr>@endforelse
</tbody></table></div>
@if($indices->hasPages())<div class="card-footer py-2">{{ $indices->links() }}</div>@endif</div>
</x-app-layout>
