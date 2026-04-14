<x-app-layout>
    <x-slot name="title">Risk &amp; Analytics</x-slot>

    <h5 class="fw-semibold mb-4">Risk &amp; Analytics</h5>

    <div class="row g-3">
        <div class="col-md-3">
            <a href="{{ route('risk.portfolio-analysis') }}" class="text-decoration-none">
                <div class="card card-etrm h-100 text-center py-4">
                    <div class="card-body">
                        <div style="font-size:2rem;">&#128202;</div>
                        <div class="fw-semibold mt-2">Portfolio Analysis</div>
                        <div class="text-muted small">Net position, MTM &amp; exposure by currency</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('risk.counterparty-exposure') }}" class="text-decoration-none">
                <div class="card card-etrm h-100 text-center py-4">
                    <div class="card-body">
                        <div style="font-size:2rem;">&#128203;</div>
                        <div class="fw-semibold mt-2">Counterparty Exposure</div>
                        <div class="text-muted small">Credit limits &amp; breach flags</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('risk.var') }}" class="text-decoration-none">
                <div class="card card-etrm h-100 text-center py-4">
                    <div class="card-body">
                        <div style="font-size:2rem;">&#128200;</div>
                        <div class="fw-semibold mt-2">VaR &amp; Stress Tests</div>
                        <div class="text-muted small">Historical VaR &amp; price shock scenarios</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('risk.reports') }}" class="text-decoration-none">
                <div class="card card-etrm h-100 text-center py-4">
                    <div class="card-body">
                        <div style="font-size:2rem;">&#128196;</div>
                        <div class="fw-semibold mt-2">Reports</div>
                        <div class="text-muted small">Generate &amp; download CSV exports</div>
                    </div>
                </div>
            </a>
        </div>
    </div>
</x-app-layout>
