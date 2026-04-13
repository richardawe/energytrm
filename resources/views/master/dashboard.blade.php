<x-app-layout>
    <x-slot name="title">Master Data</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0" style="color: var(--etrm-primary);">Master Data</h5>
    </div>

    <div class="row g-3">
        @php
        $sections = [
            ['title'=>'Currencies',        'route'=>'master.currencies.index',        'icon'=>'💱', 'desc'=>'FX rates and currency codes'],
            ['title'=>'Payment Terms',     'route'=>'master.payment-terms.index',     'icon'=>'📅', 'desc'=>'Invoice payment conditions'],
            ['title'=>'Incoterms',         'route'=>'master.incoterms.index',         'icon'=>'🚢', 'desc'=>'Trade delivery terms'],
            ['title'=>'Transport Classes', 'route'=>'master.transport-classes.index', 'icon'=>'🚛', 'desc'=>'Barge, pipeline, rail, truck'],
            ['title'=>'Parties',           'route'=>'master.parties.index',           'icon'=>'🏢', 'desc'=>'Legal entities, business units'],
            ['title'=>'Products',          'route'=>'master.products.index',          'icon'=>'🛢️', 'desc'=>'Commodities and products'],
            ['title'=>'Units of Measure',  'route'=>'master.uoms.index',              'icon'=>'⚖️', 'desc'=>'MT, BBL, MMBTU, MWh'],
            ['title'=>'Indices / Curves',  'route'=>'master.indices.index',           'icon'=>'📈', 'desc'=>'Market indices and forward curves'],
            ['title'=>'Agreements',        'route'=>'master.agreements.index',        'icon'=>'📄', 'desc'=>'Counterparty agreements'],
            ['title'=>'Brokers',           'route'=>'master.brokers.index',           'icon'=>'🤝', 'desc'=>'Brokerage firms and commission schedules'],
            ['title'=>'Portfolios',        'route'=>'master.portfolios.index',        'icon'=>'📁', 'desc'=>'Internal trading portfolios'],
        ];
        @endphp

        @foreach($sections as $s)
        <div class="col-md-3 col-sm-6">
            <a href="{{ route($s['route']) }}" class="text-decoration-none">
                <div class="card card-etrm h-100">
                    <div class="card-body p-3 d-flex gap-3 align-items-start">
                        <span style="font-size:1.5rem;line-height:1;">{{ $s['icon'] }}</span>
                        <div>
                            <div class="fw-semibold small" style="color: var(--etrm-primary);">{{ $s['title'] }}</div>
                            <div class="text-muted" style="font-size:0.75rem;">{{ $s['desc'] }}</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>
</x-app-layout>
