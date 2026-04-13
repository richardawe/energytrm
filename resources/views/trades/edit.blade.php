<x-app-layout>
    <x-slot name="title">Amend {{ $trade->deal_number }}</x-slot>

    <div class="mb-3">
        <a href="{{ route('trades.show', $trade) }}" class="text-muted small text-decoration-none">← {{ $trade->deal_number }}</a>
    </div>

    <div class="alert alert-warning py-2 mb-3">
        <strong>Amendment:</strong> Saving will generate a new Transaction Number and increment the version
        (currently v{{ $trade->version }}). The Deal Number and Instrument Number remain unchanged.
    </div>

    <form method="POST" action="{{ route('trades.update', $trade) }}" x-data="tradeForm()">
        @csrf
        @method('PUT')
        <div class="row g-3">

            <div class="col-lg-8">

                <div class="card card-etrm mb-3">
                    <div class="card-header">Deal Identity</div>
                    <div class="card-body">
                        <div class="row g-2 mb-3" style="font-size:.85rem;">
                            <div class="col-auto"><span class="text-muted">Deal No:</span> <strong>{{ $trade->deal_number }}</strong></div>
                            <div class="col-auto"><span class="text-muted">Instrument No:</span> <strong>{{ $trade->instrument_number }}</strong></div>
                            <div class="col-auto"><span class="text-muted">Current TXN:</span> <strong>{{ $trade->transaction_number }}</strong></div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Trade Date <span class="text-danger">*</span></label>
                                <input type="date" name="trade_date" class="form-control @error('trade_date') is-invalid @enderror"
                                       value="{{ old('trade_date', $trade->trade_date->format('Y-m-d')) }}">
                                @error('trade_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Delivery Start <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror"
                                       value="{{ old('start_date', $trade->start_date->format('Y-m-d')) }}">
                                @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Delivery End <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
                                       value="{{ old('end_date', $trade->end_date->format('Y-m-d')) }}">
                                @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-etrm mb-3">
                    <div class="card-header">Counterparties</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Internal BU <span class="text-danger">*</span></label>
                                <select name="internal_bu_id" class="form-select @error('internal_bu_id') is-invalid @enderror">
                                    @foreach($internalBus as $bu)
                                        <option value="{{ $bu->id }}" {{ old('internal_bu_id', $trade->internal_bu_id) == $bu->id ? 'selected' : '' }}>
                                            {{ $bu->short_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('internal_bu_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Portfolio <span class="text-danger">*</span></label>
                                <select name="portfolio_id" class="form-select @error('portfolio_id') is-invalid @enderror">
                                    @foreach($portfolios as $pf)
                                        <option value="{{ $pf->id }}" {{ old('portfolio_id', $trade->portfolio_id) == $pf->id ? 'selected' : '' }}>
                                            {{ $pf->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('portfolio_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Buy / Sell <span class="text-danger">*</span></label>
                                <select name="buy_sell" class="form-select" x-model="buySell">
                                    <option value="Buy"  {{ old('buy_sell', $trade->buy_sell) == 'Buy'  ? 'selected' : '' }}>Buy</option>
                                    <option value="Sell" {{ old('buy_sell', $trade->buy_sell) == 'Sell' ? 'selected' : '' }}>Sell</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Counterparty <span class="text-danger">*</span></label>
                                <select name="counterparty_id" class="form-select @error('counterparty_id') is-invalid @enderror">
                                    @foreach($counterparties as $cp)
                                        <option value="{{ $cp->id }}" {{ old('counterparty_id', $trade->counterparty_id) == $cp->id ? 'selected' : '' }}>
                                            {{ $cp->short_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('counterparty_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Pay / Receive</label>
                                <input type="text" class="form-control bg-light" readonly
                                       :value="buySell === 'Buy' ? 'Pay' : 'Receive'">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-etrm mb-3">
                    <div class="card-header">Product &amp; Quantity</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Product <span class="text-danger">*</span></label>
                                <select name="product_id" class="form-select">
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}" {{ old('product_id', $trade->product_id) == $p->id ? 'selected' : '' }}>
                                            {{ $p->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" step="0.0001" min="0"
                                       class="form-control @error('quantity') is-invalid @enderror"
                                       value="{{ old('quantity', $trade->quantity) }}">
                                @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">UOM <span class="text-danger">*</span></label>
                                <select name="uom_id" class="form-select">
                                    @foreach($uoms as $u)
                                        <option value="{{ $u->id }}" {{ old('uom_id', $trade->uom_id) == $u->id ? 'selected' : '' }}>
                                            {{ $u->code }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Volume Type</label>
                                <select name="volume_type" class="form-select">
                                    @foreach(['Fixed','Variable','Optional'] as $vt)
                                        <option value="{{ $vt }}" {{ old('volume_type', $trade->volume_type) == $vt ? 'selected' : '' }}>{{ $vt }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-etrm mb-3">
                    <div class="card-header">Pricing</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Fixed / Float</label>
                                <select name="fixed_float" class="form-select" x-model="fixedFloat">
                                    <option value="Fixed" {{ old('fixed_float', $trade->fixed_float) == 'Fixed' ? 'selected' : '' }}>Fixed</option>
                                    <option value="Float" {{ old('fixed_float', $trade->fixed_float) == 'Float' ? 'selected' : '' }}>Float</option>
                                </select>
                            </div>
                            <div class="col-md-4" x-show="fixedFloat === 'Fixed'">
                                <label class="form-label fw-semibold">Fixed Price</label>
                                <input type="number" name="fixed_price" step="0.000001" min="0"
                                       class="form-control" value="{{ old('fixed_price', $trade->fixed_price) }}">
                            </div>
                            <div class="col-md-5" x-show="fixedFloat === 'Float'">
                                <label class="form-label fw-semibold">Index</label>
                                <select name="index_id" class="form-select">
                                    <option value="">— select —</option>
                                    @foreach($indices as $idx)
                                        <option value="{{ $idx->id }}" {{ old('index_id', $trade->index_id) == $idx->id ? 'selected' : '' }}>
                                            {{ $idx->index_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2" x-show="fixedFloat === 'Float'">
                                <label class="form-label fw-semibold">Spread</label>
                                <input type="number" name="spread" step="0.000001"
                                       class="form-control" value="{{ old('spread', $trade->spread) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Currency</label>
                                <select name="currency_id" class="form-select">
                                    @foreach($currencies as $c)
                                        <option value="{{ $c->id }}" {{ old('currency_id', $trade->currency_id) == $c->id ? 'selected' : '' }}>
                                            {{ $c->code }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Payment Terms</label>
                                <select name="payment_terms_id" class="form-select">
                                    <option value="">— none —</option>
                                    @foreach($paymentTerms as $pt)
                                        <option value="{{ $pt->id }}" {{ old('payment_terms_id', $trade->payment_terms_id) == $pt->id ? 'selected' : '' }}>
                                            {{ $pt->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card card-etrm mb-3">
                    <div class="card-header">Logistics</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Incoterm</label>
                                <select name="incoterm_code" class="form-select">
                                    <option value="">— none —</option>
                                    @foreach($incoterms as $ic)
                                        <option value="{{ $ic->code }}" {{ old('incoterm_code', $trade->incoterm_code) == $ic->code ? 'selected' : '' }}>
                                            {{ $ic->code }} — {{ $ic->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Load Port</label>
                                <input type="text" name="load_port" class="form-control"
                                       value="{{ old('load_port', $trade->load_port) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Discharge Port</label>
                                <input type="text" name="discharge_port" class="form-control"
                                       value="{{ old('discharge_port', $trade->discharge_port) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-etrm mb-3">
                    <div class="card-header">Other</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Broker</label>
                                <select name="broker_id" class="form-select">
                                    <option value="">— none —</option>
                                    @foreach($brokers as $b)
                                        <option value="{{ $b->id }}" {{ old('broker_id', $trade->broker_id) == $b->id ? 'selected' : '' }}>
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
                                        <option value="{{ $ag->id }}" {{ old('agreement_id', $trade->agreement_id) == $ag->id ? 'selected' : '' }}>
                                            {{ $ag->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Comments</label>
                                <textarea name="comments" class="form-control" rows="3">{{ old('comments', $trade->comments) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-2 mb-4">
            <button type="submit" class="btn btn-warning fw-semibold">Save Amendment</button>
            <a href="{{ route('trades.show', $trade) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>

    <script>
        function tradeForm() {
            return {
                buySell:    '{{ old('buy_sell', $trade->buy_sell) }}',
                fixedFloat: '{{ old('fixed_float', $trade->fixed_float) }}',
            }
        }
    </script>
</x-app-layout>
