<x-app-layout>
    <x-slot name="title">Financials</x-slot>

    <h5 class="fw-semibold mb-4">Financials</h5>

    <div class="row g-3">
        <div class="col-md-4">
            <a href="{{ route('financials.market-prices.index') }}" class="text-decoration-none">
                <div class="card card-etrm h-100 text-center py-4">
                    <div class="card-body">
                        <div style="font-size:2rem;">📈</div>
                        <div class="fw-semibold mt-2">Market Prices</div>
                        <div class="text-muted small">Index price entry & history</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('financials.broker-fees.index') }}" class="text-decoration-none">
                <div class="card card-etrm h-100 text-center py-4">
                    <div class="card-body">
                        <div style="font-size:2rem;">💼</div>
                        <div class="fw-semibold mt-2">Broker Fees</div>
                        <div class="text-muted small">Commission calculations per trade</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('financials.pnl.index') }}" class="text-decoration-none">
                <div class="card card-etrm h-100 text-center py-4">
                    <div class="card-body">
                        <div style="font-size:2rem;">💹</div>
                        <div class="fw-semibold mt-2">P&amp;L View</div>
                        <div class="text-muted small">Trade value, MTM &amp; realised P&amp;L</div>
                    </div>
                </div>
            </a>
        </div>
    </div>
</x-app-layout>
