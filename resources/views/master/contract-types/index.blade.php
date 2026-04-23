<x-app-layout>
    <x-slot name="title">Contract Types</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('master.dashboard') }}" class="text-muted small text-decoration-none">Master Data</a>
            <span class="text-muted small"> / </span>
            <span class="small fw-semibold">Contract Types</span>
        </div>
        <a href="{{ route('master.contract-types.create') }}" class="btn btn-primary btn-sm"
           style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">+ New Contract Type</a>
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
                        <th>Incoterm</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contractTypes as $ct)
                    <tr>
                        <td><a href="{{ route('master.contract-types.show', $ct) }}" class="text-decoration-none fw-semibold">{{ $ct->name }}</a></td>
                        <td><code>{{ $ct->code }}</code></td>
                        <td>{{ $ct->incoterm ?? '—' }}</td>
                        <td class="text-muted">{{ $ct->description ? \Illuminate\Support\Str::limit($ct->description, 60) : '—' }}</td>
                        <td>@include('partials._status_badge', ['status' => $ct->is_active ? 'Active' : 'Inactive'])</td>
                        <td class="text-end">
                            <a href="{{ route('master.contract-types.edit', $ct) }}" class="btn btn-outline-secondary btn-xs py-0 px-2" style="font-size:.75rem;">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-3">No contract types defined.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($contractTypes->hasPages())
        <div class="card-footer py-2">{{ $contractTypes->links() }}</div>
        @endif
    </div>
</x-app-layout>
