<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    <div class="row g-3 mb-4">
        <div class="col-12">
            <h5 class="fw-bold mb-0" style="color: var(--etrm-primary);">
                Welcome back, {{ Auth::user()->name }}
            </h5>
            <div class="text-muted small">{{ now()->format('l, d F Y') }}</div>
        </div>
    </div>

    {{-- Module quick-access cards --}}
    <div class="row g-3">
        @php
        $modules = [
            ['icon'=>'📋','title'=>'Physical Trades','desc'=>'Capture and manage commodity trades','route'=>'trades.index','color'=>'#1a3c5e'],
            ['icon'=>'🚢','title'=>'Operations','desc'=>'Logistics, invoices, settlements','route'=>'operations.dashboard','color'=>'#2e6da4'],
            ['icon'=>'📊','title'=>'Financials','desc'=>'Market data, P&L, broker fees','route'=>'financials.dashboard','color'=>'#1a6e3c'],
            ['icon'=>'⚡','title'=>'Risk & Analytics','desc'=>'VaR, stress tests, credit exposure','route'=>'risk.dashboard','color'=>'#7a1a2e'],
            ['icon'=>'🗂️','title'=>'Master Data','desc'=>'Parties, products, indices, brokers','route'=>'master.dashboard','color'=>'#5a4a1a'],
            ['icon'=>'👥','title'=>'User Management','desc'=>'Personnel, roles, security groups','route'=>'admin.users.index','color'=>'#2a2a5e'],
        ];
        @endphp

        @foreach($modules as $m)
        <div class="col-md-4 col-sm-6">
            <a href="{{ route($m['route']) }}" class="text-decoration-none">
                <div class="card card-etrm h-100" style="border-top: 3px solid {{ $m['color'] }};">
                    <div class="card-body d-flex align-items-start gap-3 p-3">
                        <span style="font-size: 1.75rem; line-height:1;">{{ $m['icon'] }}</span>
                        <div>
                            <div class="fw-semibold" style="color: {{ $m['color'] }}; font-size: 0.925rem;">
                                {{ $m['title'] }}
                            </div>
                            <div class="text-muted small mt-1">{{ $m['desc'] }}</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>
</x-app-layout>
