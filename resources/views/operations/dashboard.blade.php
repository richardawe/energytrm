<x-app-layout>
    <x-slot name="title">Operations</x-slot>

    <h5 class="fw-semibold mb-4">Operations</h5>

    <div class="row g-3">
        <div class="col-md-3">
            <a href="{{ route('operations.shipments.index') }}" class="text-decoration-none">
                <div class="card card-etrm h-100 text-center py-4">
                    <div class="card-body">
                        <div style="font-size:2rem;">🚢</div>
                        <div class="fw-semibold mt-2">Shipments</div>
                        <div class="text-muted small">Logistics & delivery tracking</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('operations.invoices.index') }}" class="text-decoration-none">
                <div class="card card-etrm h-100 text-center py-4">
                    <div class="card-body">
                        <div style="font-size:2rem;">🧾</div>
                        <div class="fw-semibold mt-2">Invoices</div>
                        <div class="text-muted small">Trade invoicing & billing</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('operations.nominations.index') }}" class="text-decoration-none">
                <div class="card card-etrm h-100 text-center py-4">
                    <div class="card-body">
                        <div style="font-size:2rem;">📋</div>
                        <div class="fw-semibold mt-2">Nominations</div>
                        <div class="text-muted small">Gas/power scheduling</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('operations.eob.index') }}" class="text-decoration-none">
                <div class="card card-etrm h-100 text-center py-4">
                    <div class="card-body">
                        <div style="font-size:2rem;">✅</div>
                        <div class="fw-semibold mt-2">EoB Checklist</div>
                        <div class="text-muted small">End-of-business sign-off</div>
                    </div>
                </div>
            </a>
        </div>
    </div>
</x-app-layout>
