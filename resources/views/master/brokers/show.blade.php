<x-app-layout><x-slot name="title">{{ $broker->name }}</x-slot>
<div class="mb-3"><a href="{{ route('master.brokers.index') }}" class="text-muted small text-decoration-none">← Brokers</a></div>
<div class="row g-3">
<div class="col-md-6">
<div class="card card-etrm"><div class="card-header d-flex justify-content-between align-items-center">
    <span>{{ $broker->name }}</span>
    <div class="d-flex gap-2">@include('partials._status_badge', ['status' => $broker->status])<a href="{{ route('master.brokers.edit', $broker) }}" class="btn btn-outline-secondary btn-sm">Edit</a></div>
</div><div class="card-body">
<div class="row g-2">
    <div class="col-6"><div class="text-muted small">Type</div><div>{{ $broker->broker_type }}</div></div>
    <div class="col-6"><div class="text-muted small">Short Name</div><div>{{ $broker->short_name ?? '—' }}</div></div>
    @if($broker->lei)<div class="col-12"><div class="text-muted small">LEI</div><div><code>{{ $broker->lei }}</code></div></div>@endif
    <div class="col-6"><div class="text-muted small">Regulated</div><div>{{ $broker->is_regulated ? 'Yes' : 'No' }}</div></div>
</div></div></div>
</div>
<div class="col-md-6">
<div class="card card-etrm"><div class="card-header d-flex justify-content-between align-items-center">
    <span>Commission Schedules ({{ $broker->commissions->count() }})</span>
    <a href="{{ route('master.brokers.commissions.create', $broker) }}" class="btn btn-sm btn-primary"
       style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">+ Add Commission</a>
</div>
<div class="card-body p-0">
@if($broker->commissions->isEmpty())<div class="p-3 text-muted small">No commissions defined.</div>
@else
<table class="table table-etrm mb-0"><thead><tr><th>Name</th><th>Rate</th><th>Unit</th><th>Currency</th><th class="text-center">Default</th><th></th></tr></thead><tbody>
@foreach($broker->commissions as $c)
<tr>
    <td>{{ $c->name }}</td>
    <td>{{ $c->commission_rate }}</td>
    <td>{{ $c->rate_unit ?? '—' }}</td>
    <td>{{ $c->currency?->code ?? '—' }}</td>
    <td class="text-center">{{ $c->is_default ? '✓' : '' }}</td>
    <td class="text-end">
        <div class="d-flex gap-1 justify-content-end">
            <a href="{{ route('master.brokers.commissions.edit', $c) }}" class="btn btn-outline-secondary btn-xs py-0 px-2" style="font-size:.75rem;">Edit</a>
            <form method="POST" action="{{ route('master.brokers.commissions.destroy', $c) }}"
                  onsubmit="return confirm('Delete this commission schedule?');" class="d-inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-xs py-0 px-2" style="font-size:.75rem;">Del</button>
            </form>
        </div>
    </td>
</tr>
@endforeach
</tbody></table>
@endif
</div></div>
</div>
</div></x-app-layout>
