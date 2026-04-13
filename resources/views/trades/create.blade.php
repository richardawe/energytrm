<x-app-layout>
    <x-slot name="title">New Trade</x-slot>

    <div class="mb-3">
        <a href="{{ route('trades.index') }}" class="text-muted small text-decoration-none">← Trade Blotter</a>
    </div>

    <form method="POST" action="{{ route('trades.store') }}" x-data="tradeForm()">
        @csrf
        <div class="row g-3">

            {{-- ── Left column ──────────────────────────────────────────── --}}
            <div class="col-lg-8">

                {{-- Deal Identity --}}
                <div class="card card-etrm mb-3">
                    <div class="card-header">Deal Identity <small class="text-muted fw-normal">(IDs assigned on save)</small></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Trade Date <span class="text-danger">*</span></label>
                                <input type="date" name="trade_date" class="form-control @error('trade_date') is-invalid @enderror"
                                       value="{{ old('trade_date', date('Y-m-d')) }}">
                                @error('trade_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Delivery Start <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror"
                                       value="{{ old('start_date') }}">
                                @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Delivery End <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
                                       value="{{ old('end_date') }}">
                                @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Counterparties --}}
                <div class="card card-etrm mb-3">
                    <div class="card-header">Counterparties</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Internal BU <span class="text-danger">*</span></label>
                                <select name="internal_bu_id" class="form-select @error('internal_bu_id') is-invalid @enderror">
                                    <option value="">— select —</option>
                                    @foreach($internalBus as $bu)
                                        <option value="{{ $bu->id }}" {{ old('internal_bu_id') == $bu->id ? 'selected' : '' }}>
                                            {{ $bu->short_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('internal_bu_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Portfolio <span class="text-danger">*</span></label>
                                <select name="portfolio_id" class="form-select @error('portfolio_id') is-invalid @enderror">
                                    <option value="">— select —</option>
                                    @foreach($portfolios as $pf)
                                        <option value="{{ $pf->id }}" {{ old('portfolio_id') == $pf->id ? 'selected' : '' }}>
                                            {{ $pf->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('portfolio_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Buy / Sell <span class="text-danger">*</span></label>
                                <select name="buy_sell" class="form-select @error('buy_sell') is-invalid @enderror"
                                        x-model="buySell">
                                    <option value="">—</option>
                                    <option value="Buy"  {{ old('buy_sell') == 'Buy'  ? 'selected' : '' }}>Buy</option>
                                    <option value="Sell" {{ old('buy_sell') == 'Sell' ? 'selected' : '' }}>Sell</option>
                                </select>
                                @error('buy_sell')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Counterparty <span class="text-danger">*</span></label>
                                <select name="counterparty_id" class="form-select @error('counterparty_id') is-invalid @enderror">
                                    <option value="">— select —</option>
                                    @foreach($counterparties as $cp)
                                        <option value="{{ $cp->id }}" {{ old('counterparty_id') == $cp->id ? 'selected' : '' }}>
                                            {{ $cp->short_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('counterparty_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Pay / Receive</label>
                                <input type="text" class="form-control bg-light" readonly
                                       :value="buySell === 'Buy' ? 'Pay' : buySell === 'Sell' ? 'Receive' : '—'">
                                <div class="form-text">Auto-derived from Buy/Sell</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Product & Quantity --}}
                <div class="card card-etrm mb-3">
                    <div class="card-header">Product &amp; Quantity</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Product <span class="text-danger">*</span></label>
                                <select name="product_id" class="form-select @error('product_id') is-invalid @enderror">
                                    <option value="">— select —</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>
                                            {{ $p->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" step="0.0001" min="0"
                                       class="form-control @error('quantity') is-invalid @enderror"
                                       value="{{ old('quantity') }}">
                                @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">UOM <span class="text-danger">*</span></label>
                                <select name="uom_id" class="form-select @error('uom_id') is-invalid @enderror">
                                    <option value="">—</option>
                                    @foreach($uoms as $u)
                                        <option value="{{ $u->id }}" {{ old('uom_id') == $u->id ? 'selected' : '' }}>
                                            {{ $u->code }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('uom_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Volume Type</label>
                                <select name="volume_type" class="form-select">
                                    @foreach(['Fixed','Variable','Optional'] as $vt)
                                        <option value="{{ $vt }}" {{ old('volume_type','Fixed') == $vt ? 'selected' : '' }}>{{ $vt }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pricing --}}
                <div class="card card-etrm mb-3">
                    <div class="card-header">Pricing</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Fixed / Float <span class="text-danger">*</span></label>
                                <select name="fixed_float" class="form-select" x-model="fixedFloat">
                                    <option value="Fixed"  {{ old('fixed_float','Fixed') == 'Fixed'  ? 'selected' : '' }}>Fixed</option>
                                    <option value="Float"  {{ old('fixed_float','Fixed') == 'Float'  ? 'selected' : '' }}>Float</option>
                                </select>
                            </div>
                            <div class="col-md-4" x-show="fixedFloat === 'Fixed'">
                                <label class="form-label fw-semibold">Fixed Price <span class="text-danger">*</span></label>
                                <input type="number" name="fixed_price" step="0.000001" min="0"
                                       class="form-control @error('fixed_price') is-invalid @enderror"
                                       value="{{ old('fixed_price') }}">
                                @error('fixed_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-5" x-show="fixedFloat === 'Float'">
                                <label class="form-label fw-semibold">Index <span class="text-danger">*</span></label>
                                <select name="index_id" class="form-select @error('index_id') is-invalid @enderror">
                                    <option value="">— select index —</option>
                                    @foreach($indices as $idx)
                                        <option value="{{ $idx->id }}" {{ old('index_id') == $idx->id ? 'selected' : '' }}>
                                            {{ $idx->index_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('index_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-2" x-show="fixedFloat === 'Float'">
                                <label class="form-label fw-semibold">Spread</label>
                                <input type="number" name="spread" step="0.000001"
                                       class="form-control" value="{{ old('spread', 0) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Currency <span class="text-danger">*</span></label>
                                <select name="currency_id" class="form-select @error('currency_id') is-invalid @enderror">
                                    <option value="">—</option>
                                    @foreach($currencies as $c)
                                        <option value="{{ $c->id }}" {{ old('currency_id') == $c->id ? 'selected' : '' }}>
                                            {{ $c->code }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('currency_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Payment Terms</label>
                                <select name="payment_terms_id" class="form-select">
                                    <option value="">— none —</option>
                                    @foreach($paymentTerms as $pt)
                                        <option value="{{ $pt->id }}" {{ old('payment_terms_id') == $pt->id ? 'selected' : '' }}>
                                            {{ $pt->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ── Right column ─────────────────────────────────────────── --}}
            <div class="col-lg-4">

                {{-- Logistics --}}
                <div class="card card-etrm mb-3">
                    <div class="card-header">Logistics</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Incoterm</label>
                                <select name="incoterm_code" class="form-select">
                                    <option value="">— none —</option>
                                    @foreach($incoterms as $ic)
                                        <option value="{{ $ic->code }}" {{ old('incoterm_code') == $ic->code ? 'selected' : '' }}>
                                            {{ $ic->code }} — {{ $ic->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Load Port</label>
                                <input type="text" name="load_port" class="form-control"
                                       value="{{ old('load_port') }}" placeholder="e.g. Rotterdam">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Discharge Port</label>
                                <input type="text" name="discharge_port" class="form-control"
                                       value="{{ old('discharge_port') }}" placeholder="e.g. Singapore">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Other --}}
                <div class="card card-etrm mb-3">
                    <div class="card-header">Other</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Broker</label>
                                <select name="broker_id" class="form-select">
                                    <option value="">— none —</option>
                                    @foreach($brokers as $b)
                                        <option value="{{ $b->id }}" {{ old('broker_id') == $b->id ? 'selected' : '' }}>
                                            {{ $b->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Agreement</label>
                                <select name="agreement_id" class="form-select">
                                    <option value="">— none —</option>
                                    @foreach($agreements as $ag)
                                        <option value="{{ $ag->id }}" {{ old('agreement_id') == $ag->id ? 'selected' : '' }}>
                                            {{ $ag->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Comments</label>
                                <textarea name="comments" class="form-control" rows="3"
                                          placeholder="Optional trade notes...">{{ old('comments') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="d-flex gap-2 mt-2 mb-4">
            <button type="submit" class="btn btn-primary"
                    style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">
                Capture Trade
            </button>
            <a href="{{ route('trades.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>

    <script>
        function tradeForm() {
            return {
                buySell:    '{{ old('buy_sell', '') }}',
                fixedFloat: '{{ old('fixed_float', 'Fixed') }}',
            }
        }
    </script>
</x-app-layout>
