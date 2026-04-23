<x-app-layout><x-slot name="title">Trading Locations</x-slot>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div><a href="{{ route('master.dashboard') }}" class="text-muted small text-decoration-none">Master Data</a> / <span class="small fw-semibold">Trading Locations</span></div>
    <a href="{{ route('master.trading-locations.create') }}" class="btn btn-primary btn-sm" style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">+ New Trading Location</a>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show py-2" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card card-etrm"><div class="card-body p-0">
<table class="table table-etrm table-hover mb-0">
<thead><tr>
    <th>Name</th>
    <th>City</th>
    <th>Country</th>
    <th>Timezone</th>
    <th>Active</th>
    <th></th>
</tr></thead>
<tbody>
@forelse($tradingLocations as $loc)
<tr>
    <td class="fw-semibold">{{ $loc->name }}</td>
    <td>{{ $loc->city ?? '—' }}</td>
    <td>{{ $loc->country ?? '—' }}</td>
    <td><code>{{ $loc->timezone ?? '—' }}</code></td>
    <td>
        @if($loc->is_active)
            <span class="badge bg-success">Active</span>
        @else
            <span class="badge bg-secondary">Inactive</span>
        @endif
    </td>
    <td class="text-end">
        <a href="{{ route('master.trading-locations.edit', $loc) }}" class="btn btn-outline-secondary btn-xs py-0 px-2" style="font-size:.75rem;">Edit</a>
    </td>
</tr>
@empty
<tr><td colspan="6" class="text-center text-muted py-3">No trading locations yet.</td></tr>
@endforelse
</tbody>
</table>
</div>
@if($tradingLocations->hasPages())<div class="card-footer py-2">{{ $tradingLocations->links() }}</div>@endif
</div>
</x-app-layout>
