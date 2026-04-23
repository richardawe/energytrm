<x-app-layout>
    <x-slot name="title">Exchanges</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('master.dashboard') }}" class="text-muted small text-decoration-none">Master Data</a>
            <span class="text-muted small"> / </span>
            <span class="small fw-semibold">Exchanges</span>
        </div>
        <a href="{{ route('master.exchanges.create') }}" class="btn btn-primary btn-sm"
           style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">+ New Exchange</a>
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
                        <th>Code</th>
                        <th>Timezone</th>
                        <th>Country</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exchanges as $e)
                    <tr>
                        <td><a href="{{ route('master.exchanges.show', $e) }}" class="text-decoration-none fw-semibold">{{ $e->name }}</a></td>
                        <td><code>{{ $e->code }}</code></td>
                        <td>{{ $e->timezone ?? '—' }}</td>
                        <td>{{ $e->country ?? '—' }}</td>
                        <td>@include('partials._status_badge', ['status' => $e->is_active ? 'Active' : 'Inactive'])</td>
                        <td class="text-end">
                            <a href="{{ route('master.exchanges.edit', $e) }}" class="btn btn-outline-secondary btn-xs py-0 px-2" style="font-size:.75rem;">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-3">No exchanges defined.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($exchanges->hasPages())
        <div class="card-footer py-2">{{ $exchanges->links() }}</div>
        @endif
    </div>
</x-app-layout>
