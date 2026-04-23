<x-app-layout>
    <x-slot name="title">Commodities</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('master.dashboard') }}" class="text-muted small text-decoration-none">Master Data</a>
            <span class="text-muted small"> / </span>
            <span class="small fw-semibold">Commodities</span>
        </div>
        <a href="{{ route('master.commodities.create') }}" class="btn btn-primary btn-sm"
           style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">+ New Commodity</a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card card-etrm">
        <div class="card-body p-0">
            <table class="table table-etrm table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Group</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commodities as $c)
                    <tr>
                        <td><a href="{{ route('master.commodities.show', $c) }}" class="text-decoration-none fw-semibold">{{ $c->name }}</a></td>
                        <td><span class="badge text-bg-secondary">{{ $c->commodity_group }}</span></td>
                        <td class="text-muted">{{ $c->description ? \Illuminate\Support\Str::limit($c->description, 60) : '—' }}</td>
                        <td>@include('partials._status_badge', ['status' => $c->is_active ? 'Active' : 'Inactive'])</td>
                        <td class="text-end">
                            <a href="{{ route('master.commodities.edit', $c) }}" class="btn btn-outline-secondary btn-xs py-0 px-2" style="font-size:.75rem;">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">No commodities defined.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($commodities->hasPages())
        <div class="card-footer py-2">{{ $commodities->links() }}</div>
        @endif
    </div>
</x-app-layout>
