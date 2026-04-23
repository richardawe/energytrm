<x-app-layout>
    <x-slot name="title">Security Groups</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('master.dashboard') }}" class="text-muted small text-decoration-none">Master Data</a>
            <span class="text-muted small"> / </span>
            <span class="small fw-semibold">Security Groups</span>
        </div>
        <a href="{{ route('master.security-groups.create') }}" class="btn btn-primary btn-sm"
           style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">+ New Security Group</a>
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
                        <th>Description</th>
                        <th>Active</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($securityGroups as $sg)
                    <tr>
                        <td><strong>{{ $sg->name }}</strong></td>
                        <td class="text-muted small">{{ $sg->description ?? '—' }}</td>
                        <td>{{ $sg->is_active ? '✓' : '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('master.security-groups.edit', $sg) }}"
                               class="btn btn-outline-secondary btn-xs py-0 px-2" style="font-size:.75rem;">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-muted text-center py-3">No security groups defined yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($securityGroups->hasPages())
        <div class="card-footer py-2">{{ $securityGroups->links() }}</div>
        @endif
    </div>
</x-app-layout>
