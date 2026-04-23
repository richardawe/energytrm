<x-app-layout>
    <x-slot name="title">Stress Scenarios</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('risk.dashboard') }}" class="text-muted small text-decoration-none">Risk &amp; Analytics</a>
            <span class="text-muted small"> / </span>
            <span class="small fw-semibold">Stress Scenarios</span>
        </div>
        <a href="{{ route('risk.stress-scenarios.create') }}" class="btn btn-primary btn-sm"
           style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">+ New Scenario</a>
    </div>

    <div class="card card-etrm">
        <div class="card-header fw-semibold">Stress Test Scenarios</div>
        <div class="card-body p-0">
            <table class="table table-etrm table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th class="text-center">Shocks</th>
                        <th class="text-center">Active</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($scenarios as $scenario)
                    <tr>
                        <td>
                            <a href="{{ route('risk.stress-scenarios.show', $scenario) }}"
                               class="fw-semibold text-decoration-none">{{ $scenario->name }}</a>
                        </td>
                        <td class="text-muted" style="max-width:300px;">
                            {{ Str::limit($scenario->description, 80) }}
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary">{{ $scenario->shocks_count }}</span>
                        </td>
                        <td class="text-center">{{ $scenario->is_active ? '✓' : '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('risk.stress-scenarios.show', $scenario) }}"
                               class="btn btn-outline-secondary btn-xs py-0 px-2" style="font-size:.75rem;">View</a>
                            <a href="{{ route('risk.stress-scenarios.edit', $scenario) }}"
                               class="btn btn-outline-secondary btn-xs py-0 px-2" style="font-size:.75rem;">Edit</a>
                            <form method="POST" action="{{ route('risk.stress-scenarios.destroy', $scenario) }}"
                                  class="d-inline"
                                  onsubmit="return confirm('Delete scenario \'{{ addslashes($scenario->name) }}\'?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-xs py-0 px-2"
                                        style="font-size:.75rem;">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No stress scenarios defined yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($scenarios->hasPages())
        <div class="card-footer py-2">{{ $scenarios->links() }}</div>
        @endif
    </div>
</x-app-layout>
