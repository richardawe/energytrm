<x-app-layout><x-slot name="title">{{ $index->index_name }}</x-slot>
<div class="mb-3"><a href="{{ route('master.indices.index') }}" class="text-muted small text-decoration-none">← Indices</a></div>
<div class="row g-3">
<div class="col-md-5">
<div class="card card-etrm"><div class="card-header d-flex justify-content-between align-items-center">
    <span>{{ $index->index_name }}</span>
    <div class="d-flex gap-2 align-items-center">
        <span class="text-muted small">v{{ $index->version }}</span>
        @include('partials._status_badge', ['status' => $index->rec_status])
        <a href="{{ route('master.indices.edit', $index) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
    </div>
</div><div class="card-body">
<div class="row g-2">
    <div class="col-6"><div class="text-muted small">Market</div><div>{{ $index->market ?? '—' }}</div></div>
    <div class="col-6"><div class="text-muted small">Index Group</div><div>{{ $index->index_group ?? '—' }}</div></div>
    <div class="col-6"><div class="text-muted small">Format</div><div>{{ $index->format }}</div></div>
    <div class="col-6"><div class="text-muted small">Class</div><div>{{ $index->class ?? '—' }}</div></div>
    <div class="col-6"><div class="text-muted small">Currency</div><div>{{ $index->baseCurrency?->code ?? '—' }}</div></div>
    <div class="col-6"><div class="text-muted small">UOM</div><div>{{ $index->uom?->code ?? '—' }}</div></div>
    <div class="col-6"><div class="text-muted small">Status</div><div>{{ $index->status }}</div></div>
</div></div></div>
</div>
<div class="col-md-7">
<div class="card card-etrm"><div class="card-header d-flex justify-content-between align-items-center">
    <span>Price Grid Points ({{ $index->gridPoints->count() }})</span>
    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addPriceModal">+ Add Price</button>
</div><div class="card-body p-0" style="max-height:400px;overflow-y:auto;">
<table class="table table-etrm mb-0"><thead><tr><th>Date</th><th class="text-end">Price</th><th>Entered By</th></tr></thead><tbody>
@forelse($index->gridPoints->sortByDesc('price_date') as $gp)
<tr><td>{{ $gp->price_date->format('d M Y') }}</td><td class="text-end fw-semibold">{{ number_format($gp->price, 4) }}</td><td class="text-muted">{{ $gp->enteredBy?->name ?? 'System' }}</td></tr>
@empty<tr><td colspan="3" class="text-center text-muted py-3">No price data yet.</td></tr>@endforelse
</tbody></table>
</div></div>
</div>
</div>

{{-- Add Price Modal --}}
<div class="modal fade" id="addPriceModal" tabindex="-1">
    <div class="modal-dialog modal-sm"><div class="modal-content">
        <div class="modal-header py-2"><h6 class="modal-title">Add Grid Point</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
        <form method="POST" action="{{ route('master.indices.update', $index) }}">@csrf @method('PUT')
            <input type="hidden" name="add_grid_point" value="1">
            {{-- Pass through all existing index fields --}}
            <input type="hidden" name="index_name" value="{{ $index->index_name }}">
            <input type="hidden" name="market" value="{{ $index->market }}">
            <input type="hidden" name="index_group" value="{{ $index->index_group }}">
            <input type="hidden" name="format" value="{{ $index->format }}">
            <input type="hidden" name="class" value="{{ $index->class }}">
            <input type="hidden" name="base_currency_id" value="{{ $index->base_currency_id }}">
            <input type="hidden" name="uom_id" value="{{ $index->uom_id }}">
            <input type="hidden" name="status" value="{{ $index->status }}">
            <input type="hidden" name="rec_status" value="{{ $index->rec_status }}">
            <div class="mb-3"><label class="form-label fw-semibold">Date *</label><input type="date" name="grid_date" class="form-control" value="{{ today()->format('Y-m-d') }}" required></div>
            <div class="mb-3"><label class="form-label fw-semibold">Price *</label><input type="number" name="grid_price" class="form-control" step="0.000001" required placeholder="e.g. 85.42"></div>
            <button type="submit" class="btn btn-primary btn-sm w-100" style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Add Price</button>
        </form>
        </div>
    </div></div>
</div>
</x-app-layout>
