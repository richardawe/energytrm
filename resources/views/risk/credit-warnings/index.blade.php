<x-app-layout>
    <x-slot name="title">Credit Warning Thresholds</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('risk.dashboard') }}" class="text-muted small text-decoration-none">Risk &amp; Analytics</a>
            <span class="text-muted small"> / </span>
            <span class="small fw-semibold">Credit Warning Thresholds</span>
        </div>
        <a href="{{ route('risk.credit-warnings.create') }}" class="btn btn-primary btn-sm"
           style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">+ New Threshold</a>
    </div>

    <div class="card card-etrm">
        <div class="card-header fw-semibold">Credit Warning Thresholds</div>
        <div class="card-body p-0">
            <table class="table table-etrm table-hover mb-0">
                <thead>
                    <tr>
                        <th>Counterparty</th>
                        <th class="text-end">Warning Threshold</th>
                        <th class="text-end">Breach Threshold</th>
                        <th class="text-center">Active</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($thresholds as $threshold)
                    <tr>
                        <td>
                            <strong>{{ $threshold->party?->short_name }}</strong>
                            @if($threshold->party?->long_name)
                            <br><span class="text-muted" style="font-size:.75rem;">{{ $threshold->party->long_name }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <span class="text-warning fw-semibold">{{ number_format((float)$threshold->warning_threshold_pct, 2) }}%</span>
                        </td>
                        <td class="text-end">
                            <span class="text-danger fw-semibold">{{ number_format((float)$threshold->breach_threshold_pct, 2) }}%</span>
                        </td>
                        <td class="text-center">{{ $threshold->is_active ? '✓' : '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('risk.credit-warnings.edit', $threshold) }}"
                               class="btn btn-outline-secondary btn-xs py-0 px-2" style="font-size:.75rem;">Edit</a>
                            <form method="POST" action="{{ route('risk.credit-warnings.destroy', $threshold) }}"
                                  class="d-inline"
                                  onsubmit="return confirm('Delete this threshold?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-xs py-0 px-2"
                                        style="font-size:.75rem;">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No credit warning thresholds defined yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="text-muted small mt-3">
        Warning threshold triggers a near-limit alert on the Counterparty Exposure screen.
        Breach threshold triggers a BREACH flag. Both are expressed as a percentage of the party's credit limit.
    </div>
</x-app-layout>
