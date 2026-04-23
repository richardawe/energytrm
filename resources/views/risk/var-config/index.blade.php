<x-app-layout>
    <x-slot name="title">VaR Configurations</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('risk.dashboard') }}" class="text-muted small text-decoration-none">Risk &amp; Analytics</a>
            <span class="text-muted small"> / </span>
            <span class="small fw-semibold">VaR Configuration</span>
        </div>
        <a href="{{ route('risk.var-config.create') }}" class="btn btn-primary btn-sm"
           style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">+ New Configuration</a>
    </div>

    <div class="card card-etrm">
        <div class="card-header fw-semibold">VaR Configurations</div>
        <div class="card-body p-0">
            <table class="table table-etrm table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th class="text-end">Lookback (days)</th>
                        <th class="text-end">Holding Period</th>
                        <th>Method</th>
                        <th class="text-end">Confidence Level</th>
                        <th class="text-center">Active</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($configs as $config)
                    <tr class="{{ $config->is_active ? 'table-success' : '' }}">
                        <td>
                            <strong>{{ $config->name }}</strong>
                            @if($config->is_active)
                                <span class="badge bg-success ms-1" style="font-size:.65rem;">Active</span>
                            @endif
                        </td>
                        <td class="text-end">{{ number_format($config->lookback_period_days) }}</td>
                        <td class="text-end">{{ $config->holding_period_days }}-day</td>
                        <td>{{ $config->var_method }}</td>
                        <td class="text-end">{{ number_format((float)$config->confidence_level * 100, 2) }}%</td>
                        <td class="text-center">{{ $config->is_active ? '✓' : '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('risk.var-config.edit', $config) }}"
                               class="btn btn-outline-secondary btn-xs py-0 px-2" style="font-size:.75rem;">Edit</a>
                            <form method="POST" action="{{ route('risk.var-config.destroy', $config) }}"
                                  class="d-inline"
                                  onsubmit="return confirm('Delete this VaR configuration?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-xs py-0 px-2"
                                        style="font-size:.75rem;">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No VaR configurations defined yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="text-muted small mt-3">
        The active configuration defines parameters used when running the VaR calculation.
        Confidence level must be between 0.9 and 0.9999 (e.g. 0.9500 = 95%, 0.9900 = 99%).
    </div>
</x-app-layout>
