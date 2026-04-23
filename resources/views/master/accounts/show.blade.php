<x-app-layout><x-slot name="title">Account — {{ $account->account_number }}</x-slot>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div><a href="{{ route('master.accounts.index') }}" class="text-muted small text-decoration-none">← Accounts</a></div>
    <a href="{{ route('master.accounts.edit', $account) }}" class="btn btn-primary btn-sm" style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Edit</a>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show py-2" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card card-etrm" style="max-width:700px;">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><code>{{ $account->account_number }}</code> — {{ $account->account_name }}</span>
        @include('partials._status_badge', ['status' => $account->status])
    </div>
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-4 text-muted">Account Type</dt>
            <dd class="col-sm-8">{{ $account->account_type }}</dd>

            <dt class="col-sm-4 text-muted">Holding Party</dt>
            <dd class="col-sm-8">{{ $account->holdingParty?->short_name ?? '—' }}
                @if($account->holdingParty)<span class="text-muted small">— {{ $account->holdingParty->long_name }}</span>@endif
            </dd>

            <dt class="col-sm-4 text-muted">Currency</dt>
            <dd class="col-sm-8">{{ $account->currency?->code ?? '—' }}
                @if($account->currency)<span class="text-muted small">— {{ $account->currency->name }}</span>@endif
            </dd>

            <dt class="col-sm-4 text-muted">Class</dt>
            <dd class="col-sm-8">{{ $account->class ?? '—' }}</dd>

            <dt class="col-sm-4 text-muted">Account Legal Name</dt>
            <dd class="col-sm-8">{{ $account->account_legal_name ?? '—' }}</dd>

            <dt class="col-sm-4 text-muted">Country</dt>
            <dd class="col-sm-8">{{ $account->country ?? '—' }}</dd>

            <dt class="col-sm-4 text-muted">Date Opened</dt>
            <dd class="col-sm-8">{{ $account->date_opened?->format('d M Y') ?? '—' }}</dd>

            <dt class="col-sm-4 text-muted">Date Closed</dt>
            <dd class="col-sm-8">{{ $account->date_closed?->format('d M Y') ?? '—' }}</dd>

            <dt class="col-sm-4 text-muted">General Ledger Acct</dt>
            <dd class="col-sm-8">{{ $account->general_ledger_account ?? '—' }}</dd>

            <dt class="col-sm-4 text-muted">On Balance Sheet</dt>
            <dd class="col-sm-8">{{ $account->on_balance_sheet ? 'Yes' : 'No' }}</dd>

            <dt class="col-sm-4 text-muted">Allow Multiple Units</dt>
            <dd class="col-sm-8">{{ $account->allow_multiple_units ? 'Yes' : 'No' }}</dd>

            <dt class="col-sm-4 text-muted">Sweep Enabled</dt>
            <dd class="col-sm-8">{{ $account->sweep_enabled ? 'Yes' : 'No' }}</dd>

            @if($account->description)
            <dt class="col-sm-4 text-muted">Description</dt>
            <dd class="col-sm-8">{{ $account->description }}</dd>
            @endif

            <dt class="col-sm-4 text-muted">Version</dt>
            <dd class="col-sm-8">v{{ $account->version }}</dd>

            <dt class="col-sm-4 text-muted">Created By</dt>
            <dd class="col-sm-8">{{ $account->author?->name ?? '—' }}</dd>

            <dt class="col-sm-4 text-muted">Created At</dt>
            <dd class="col-sm-8 text-muted small">{{ $account->created_at->format('d M Y H:i') }}</dd>

            <dt class="col-sm-4 text-muted">Updated At</dt>
            <dd class="col-sm-8 text-muted small">{{ $account->updated_at->format('d M Y H:i') }}</dd>
        </dl>
    </div>
</div>
</x-app-layout>
