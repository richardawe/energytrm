<x-app-layout>
    <x-slot name="title">{{ $stressScenario->name }}</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('risk.dashboard') }}" class="text-muted small text-decoration-none">Risk &amp; Analytics</a>
            <span class="text-muted small"> / </span>
            <a href="{{ route('risk.stress-scenarios.index') }}" class="text-muted small text-decoration-none">Stress Scenarios</a>
            <span class="text-muted small"> / </span>
            <span class="small fw-semibold">{{ $stressScenario->name }}</span>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('risk.stress-scenarios.edit', $stressScenario) }}"
               class="btn btn-outline-secondary btn-sm">Edit</a>
            <form method="POST" action="{{ route('risk.stress-scenarios.destroy', $stressScenario) }}"
                  onsubmit="return confirm('Delete this scenario?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-8">
            <div class="card card-etrm h-100">
                <div class="card-header fw-semibold">Scenario Details</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted">Name</dt>
                        <dd class="col-sm-8">{{ $stressScenario->name }}</dd>
                        <dt class="col-sm-4 text-muted">Description</dt>
                        <dd class="col-sm-8">{{ $stressScenario->description ?: '—' }}</dd>
                        <dt class="col-sm-4 text-muted">Status</dt>
                        <dd class="col-sm-8">
                            @if($stressScenario->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </dd>
                        <dt class="col-sm-4 text-muted">Created</dt>
                        <dd class="col-sm-8">{{ $stressScenario->created_at->format('d M Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-etrm h-100 text-center py-4">
                <div class="text-muted small">Total Shocks</div>
                <div class="fw-bold fs-3">{{ $stressScenario->shocks->count() }}</div>
                <div class="text-muted small">price index shocks defined</div>
            </div>
        </div>
    </div>

    <div class="card card-etrm">
        <div class="card-header fw-semibold">Price Shocks</div>
        <div class="card-body p-0">
            <table class="table table-etrm table-hover mb-0">
                <thead>
                    <tr>
                        <th>Index</th>
                        <th class="text-end">Price Shock</th>
                        <th class="text-center">Direction</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stressScenario->shocks as $shock)
                    <tr>
                        <td>{{ $shock->index?->index_name ?? '—' }}</td>
                        <td class="text-end fw-semibold {{ $shock->price_shock_pct < 0 ? 'text-danger' : 'text-success' }}">
                            {{ $shock->price_shock_pct > 0 ? '+' : '' }}{{ number_format((float)$shock->price_shock_pct, 2) }}%
                        </td>
                        <td class="text-center">
                            @if($shock->price_shock_pct < 0)
                                <span class="badge bg-danger">Down</span>
                            @elseif($shock->price_shock_pct > 0)
                                <span class="badge bg-success">Up</span>
                            @else
                                <span class="badge bg-secondary">Neutral</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-3">No shocks defined for this scenario.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
