<x-app-layout>
    <x-slot name="title">Governing Bodies</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('master.dashboard') }}" class="text-muted small text-decoration-none">Master Data</a>
            <span class="text-muted small"> / </span>
            <span class="small fw-semibold">Governing Bodies</span>
        </div>
        <a href="{{ route('master.governing-bodies.create') }}" class="btn btn-primary btn-sm"
           style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">+ New Governing Body</a>
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
                        <th>Jurisdiction</th>
                        <th>Country</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($governingBodies as $gb)
                    <tr>
                        <td><a href="{{ route('master.governing-bodies.show', $gb) }}" class="text-decoration-none fw-semibold">{{ $gb->name }}</a></td>
                        <td>{{ $gb->jurisdiction ?? '—' }}</td>
                        <td>{{ $gb->country ?? '—' }}</td>
                        <td>@include('partials._status_badge', ['status' => $gb->is_active ? 'Active' : 'Inactive'])</td>
                        <td class="text-end">
                            <a href="{{ route('master.governing-bodies.edit', $gb) }}" class="btn btn-outline-secondary btn-xs py-0 px-2" style="font-size:.75rem;">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">No governing bodies defined.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($governingBodies->hasPages())
        <div class="card-footer py-2">{{ $governingBodies->links() }}</div>
        @endif
    </div>
</x-app-layout>
