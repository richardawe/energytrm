<x-app-layout><x-slot name="title">Settlement Instructions</x-slot>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Settlement Instructions</h5>
    <a href="{{ route('master.settlement-instructions.create') }}" class="btn btn-primary btn-sm" style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">+ New SI</a>
</div>
<div class="card card-etrm">
<div class="card-body p-0">
@if($instructions->isEmpty())
    <p class="text-muted p-3 mb-0">No settlement instructions found.</p>
@else
<table class="table table-etrm table-hover mb-0">
<thead><tr>
    <th>SI Number</th>
    <th>Name</th>
    <th>Party</th>
    <th>Payment Method</th>
    <th>Status</th>
    <th>DVP</th>
    <th></th>
</tr></thead>
<tbody>
@foreach($instructions as $si)
<tr>
    <td><a href="{{ route('master.settlement-instructions.show', $si) }}" class="text-decoration-none fw-semibold">{{ $si->si_number }}</a></td>
    <td>{{ $si->si_name }}</td>
    <td>{{ $si->party?->short_name ?? '—' }}</td>
    <td>{{ $si->payment_method ?? '—' }}</td>
    <td>@include('partials._status_badge', ['status' => $si->status])</td>
    <td>{{ $si->is_dvp ? '✓' : '' }}</td>
    <td class="text-end">
        <a href="{{ route('master.settlement-instructions.edit', $si) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
    </td>
</tr>
@endforeach
</tbody>
</table>
<div class="p-3">{{ $instructions->links() }}</div>
@endif
</div>
</div>
</x-app-layout>
