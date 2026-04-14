<x-app-layout>
    <x-slot name="title">Audit Log</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="small fw-semibold">Audit Log</span>
        <span class="text-muted small">All system actions across all modules</span>
    </div>

    <form method="GET" class="filter-bar mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-auto">
                <select name="action" class="form-select form-select-sm">
                    <option value="">All Actions</option>
                    @foreach(['created','updated','validated','reverted','deleted'] as $a)
                    <option value="{{ $a }}" {{ request('action') === $a ? 'selected' : '' }}>{{ ucfirst($a) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">All Users</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    <option value="Trade" {{ request('type') === 'Trade' ? 'selected' : '' }}>Trade</option>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-secondary">Filter</button>
                <a href="{{ route('admin.audit.index') }}" class="btn btn-sm btn-link text-muted">Clear</a>
            </div>
        </div>
    </form>

    <div class="card card-etrm">
        <div class="card-body p-0">
            <table class="table table-etrm table-hover mb-0" style="font-size:.85rem;">
                <thead>
                    <tr>
                        <th style="width:110px;">Action</th>
                        <th>Record</th>
                        <th>User</th>
                        <th>Changes</th>
                        <th>IP</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>
                            <span class="badge {{ $log->actionBadgeClass() }}">{{ ucfirst($log->action) }}</span>
                        </td>
                        <td>
                            @php $shortType = class_basename($log->auditable_type); @endphp
                            <span class="text-muted small">{{ $shortType }}</span>
                            <strong class="ms-1">#{{ $log->auditable_id }}</strong>
                            {{-- Link to trade if applicable --}}
                            @if($shortType === 'Trade')
                            <a href="{{ route('trades.show', $log->auditable_id) }}"
                               class="ms-1 text-muted" style="font-size:.75rem;">↗</a>
                            @endif
                        </td>
                        <td>{{ $log->user?->name ?? '<em class="text-muted">system</em>' }}</td>
                        <td class="text-muted small" style="max-width:320px;">
                            @if($log->action === 'updated' && $log->old_values && $log->new_values)
                                @php
                                    $skip = ['updated_at','created_at','remember_token'];
                                    $changed = collect($log->new_values)
                                        ->filter(fn($v,$k) => !in_array($k,$skip)
                                            && ($log->old_values[$k] ?? null) != $v)
                                        ->keys()
                                        ->map(fn($k) => str_replace('_',' ',ucfirst($k)));
                                @endphp
                                {{ $changed->join(', ') ?: '—' }}
                            @elseif($log->action === 'created')
                                New record
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-muted small">{{ $log->ip_address }}</td>
                        <td class="text-muted small" style="white-space:nowrap;">{{ $log->created_at->format('d-M-Y H:i:s') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No audit entries found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
        <div class="card-footer py-2">{{ $logs->links() }}</div>
        @endif
    </div>
</x-app-layout>
