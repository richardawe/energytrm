<x-app-layout>
    <x-slot name="title">New Trade</x-slot>

    <div class="mb-3">
        <a href="{{ route('trades.index') }}" class="text-muted small text-decoration-none">← Trade Blotter</a>
    </div>

    <form method="POST" action="{{ route('trades.store') }}"
          x-data="tradeForm({{ json_encode($pipelineCascade) }}, {{ json_encode($productIndexMap) }})">
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
                                <label class="form-label fw-semibold">Trade Date <span class="text-danger">*</span><x-field-tip field="Trade Date" tab="Physical Trades" /></label>
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
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Internal BU <span class="text-danger">*</span></label>
                                <select name="internal_bu_id" class="form-select @error('internal_bu_id') is-invalid @enderror">
                                    <option value="">— select —</option>
                                    @foreach($internalBus as $bu)
                                        <option value="{{ $bu->id }}" {{ old('internal_bu_id') == $bu->id ? 'selected' : '' }}>{{ $bu->short_name }}</option>
                                    @endforeach
                                </select>
                                @error('internal_bu_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Portfolio <span class="text-danger">*</span></label>
                                <select name="portfolio_id" class="form-select @error('portfolio_id') is-invalid @enderror">
                                    <option value="">— select —</option>
                                    @foreach($portfolios as $pf)
                                        <option value="{{ $pf->id }}" {{ old('portfolio_id') == $pf->id ? 'selected' : '' }}>{{ $pf->name }}</option>
                                    @endforeach
                                </select>
                                @error('portfolio_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Trader</label>
                                <select name="trader_id" class="form-select">
                                    <option value="">— defaults to you —</option>
                                    @foreach($traders as $t)
                                        <option value="{{ $t->id }}" {{ old('trader_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Defaults to logged-in user if blank</div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Counterparty <span class="text-danger">*</span></label>
                                <select name="counterparty_id" class="form-select @error('counterparty_id') is-invalid @enderror">
                                    <option value="">— select —</option>
                                    @foreach($counterparties as $cp)
                                        <option value="{{ $cp->id }}" {{ old('counterparty_id') == $cp->id ? 'selected' : '' }}>{{ $cp->short_name }}</option>
                                    @endforeach
                                </select>
                                @error('counterparty_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Buy / Sell <span class="text-danger">*</span></label>
                                <select name="buy_sell" class="form-select @error('buy_sell') is-invalid @enderror" x-model="buySell">
                                    <option value="">—</option>
                                    <option value="Buy"  {{ old('buy_sell') == 'Buy'  ? 'selected' : '' }}>Buy</option>
                                    <option value="Sell" {{ old('buy_sell') == 'Sell' ? 'selected' : '' }}>Sell</option>
                                </select>
                                @error('buy_sell')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                                <select name="product_id" class="form-select @error('product_id') is-invalid @enderror"
                                        x-model="productId" @change="onProductChange()">
                                    <option value="">— select —</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
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
                                        <option value="{{ $u->id }}" {{ old('uom_id') == $u->id ? 'selected' : '' }}>{{ $u->code }}</option>
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
                                    <option value="Fixed" {{ old('fixed_float','Fixed') == 'Fixed' ? 'selected' : '' }}>Fixed</option>
                                    <option value="Float" {{ old('fixed_float','Fixed') == 'Float' ? 'selected' : '' }}>Float</option>
                                </select>
                            </div>
                            <div class="col-md-3" x-show="fixedFloat === 'Fixed'">
                                <label class="form-label fw-semibold">Fixed Price <span class="text-danger">*</span></label>
                                <input type="number" name="fixed_price" step="0.000001" min="0"
                                       class="form-control @error('fixed_price') is-invalid @enderror"
                                       value="{{ old('fixed_price') }}">
                                @error('fixed_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4" x-show="fixedFloat === 'Float'">
                                <label class="form-label fw-semibold">Index <span class="text-danger">*</span></label>
                                <select name="index_id" class="form-select @error('index_id') is-invalid @enderror">
                                    <option value="">— select index —</option>
                                    @foreach($indices as $idx)
                                        <option value="{{ $idx->id }}"
                                                data-product="{{ $idx->product_id }}"
                                                {{ old('index_id') == $idx->id ? 'selected' : '' }}
                                                x-show="!productId || !productIndexMap[productId] || productIndexMap[productId].includes({{ $idx->id }})">
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
                            <div class="col-md-3" x-show="fixedFloat === 'Float'">
                                <label class="form-label fw-semibold">Reference Source</label>
                                <select name="reference_source" class="form-select">
                                    <option value="">— any —</option>
                                    @foreach(['Platts','Argus','ICE Settle','NYMEX Settle','Heren','Bloomberg'] as $src)
                                        <option value="{{ $src }}" {{ old('reference_source') == $src ? 'selected' : '' }}>{{ $src }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Price Unit</label>
                                <select name="price_unit_id" class="form-select">
                                    <option value="">— same as UOM —</option>
                                    @foreach($uoms as $u)
                                        <option value="{{ $u->id }}" {{ old('price_unit_id') == $u->id ? 'selected' : '' }}>{{ $u->code }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Only if price UOM differs from volume UOM</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Put / Call</label>
                                <select name="put_call" class="form-select">
                                    <option value="">— none —</option>
                                    <option value="Put"  {{ old('put_call') == 'Put'  ? 'selected' : '' }}>Put</option>
                                    <option value="Call" {{ old('put_call') == 'Call' ? 'selected' : '' }}>Call</option>
                                </select>
                                <div class="form-text">For embedded optionality</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Currency <span class="text-danger">*</span></label>
                                <select name="currency_id" class="form-select @error('currency_id') is-invalid @enderror">
                                    <option value="">—</option>
                                    @foreach($currencies as $c)
                                        <option value="{{ $c->id }}" {{ old('currency_id') == $c->id ? 'selected' : '' }}>{{ $c->code }}</option>
                                    @endforeach
                                </select>
                                @error('currency_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Payment Terms</label>
                                <select name="payment_terms_id" class="form-select">
                                    <option value="">— none —</option>
                                    @foreach($paymentTerms as $pt)
                                        <option value="{{ $pt->id }}" {{ old('payment_terms_id') == $pt->id ? 'selected' : '' }}>{{ $pt->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Logistics --}}
                <div class="card card-etrm mb-3">
                    <div class="card-header">Logistics &amp; Delivery</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Incoterm</label>
                                <select name="incoterm_code" class="form-select">
                                    <option value="">— none —</option>
                                    @foreach($incoterms as $ic)
                                        <option value="{{ $ic->code }}" {{ old('incoterm_code') == $ic->code ? 'selected' : '' }}>{{ $ic->code }} — {{ $ic->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Load Port</label>
                                <input type="text" name="load_port" class="form-control"
                                       value="{{ old('load_port') }}" placeholder="e.g. Rotterdam">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Discharge Port</label>
                                <input type="text" name="discharge_port" class="form-control"
                                       value="{{ old('discharge_port') }}" placeholder="e.g. Singapore">
                            </div>

                            {{-- Pipeline / Zone / Location (gas & power) --}}
                            @if($pipelines->isNotEmpty())
                            <div class="col-12"><hr class="my-1"><small class="text-muted fw-semibold">Pipeline Path (gas / power)</small></div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Pipeline</label>
                                <select name="pipeline_id" class="form-select"
                                        x-model="pipelineId" @change="onPipelineChange()">
                                    <option value="">— none —</option>
                                    @foreach($pipelines as $pl)
                                        <option value="{{ $pl->id }}" {{ old('pipeline_id') == $pl->id ? 'selected' : '' }}>
                                            {{ $pl->code }} — {{ $pl->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Zone</label>
                                <select name="zone_id" class="form-select"
                                        x-model="zoneId" @change="onZoneChange()"
                                        :disabled="!pipelineId">
                                    <option value="">— select pipeline first —</option>
                                    <template x-for="z in availableZones" :key="z.id">
                                        <option :value="z.id" x-text="z.zone_code + ' — ' + z.zone_name"
                                                :selected="z.id == {{ old('zone_id', 0) }}"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Location</label>
                                <select name="location_id" class="form-select" :disabled="!zoneId">
                                    <option value="">— select zone first —</option>
                                    <template x-for="l in availableLocations" :key="l.id">
                                        <option :value="l.id"
                                                x-text="l.location_code + ' (' + l.location_type + ') — ' + l.location_name"
                                                :selected="l.id == {{ old('location_id', 0) }}"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Fuel %</label>
                                <input type="number" name="fuel_percent" step="0.0001" min="0" max="100"
                                       class="form-control" value="{{ old('fuel_percent') }}"
                                       placeholder="e.g. 1.25">
                                <div class="form-text">Pipeline fuel shrinkage</div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>

            {{-- ── Right column ─────────────────────────────────────────── --}}
            <div class="col-lg-4">

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
                                        <option value="{{ $b->id }}" {{ old('broker_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Agreement</label>
                                <select name="agreement_id" class="form-select">
                                    <option value="">— none —</option>
                                    @foreach($agreements as $ag)
                                        <option value="{{ $ag->id }}" {{ old('agreement_id') == $ag->id ? 'selected' : '' }}>{{ $ag->name }}</option>
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

                {{-- Hedge Link --}}
                @if($financialTrades->isNotEmpty())
                <div class="card card-etrm mb-3">
                    <div class="card-header">Hedge Link <small class="text-muted fw-normal">(optional)</small></div>
                    <div class="card-body">
                        <label class="form-label fw-semibold">Hedged By Financial Trade</label>
                        <select name="hedged_by_financial_trade_id" class="form-select">
                            <option value="">— none —</option>
                            @foreach($financialTrades as $ft)
                                <option value="{{ $ft->id }}" {{ old('hedged_by_financial_trade_id') == $ft->id ? 'selected' : '' }}>
                                    {{ $ft->deal_number }} — {{ ucfirst($ft->instrument_type) }} ({{ $ft->trade_status }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Link a swap, futures or options trade that hedges this physical position.</div>
                    </div>
                </div>
                @endif

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
        function tradeForm(pipelineCascade, productIndexMap) {
            return {
                buySell:            '{{ old('buy_sell', '') }}',
                fixedFloat:         '{{ old('fixed_float', 'Fixed') }}',
                productId:          '{{ old('product_id', '') }}',
                pipelineId:         '{{ old('pipeline_id', '') }}',
                zoneId:             '{{ old('zone_id', '') }}',
                availableZones:     [],
                availableLocations: [],
                productIndexMap:    productIndexMap,

                init() {
                    if (this.pipelineId) { this.onPipelineChange(); }
                    if (this.zoneId)     { this.onZoneChange(); }
                },

                onProductChange() {
                    // Index dropdown is filtered via x-show on each option
                },

                onPipelineChange() {
                    this.availableZones     = pipelineCascade[this.pipelineId] || [];
                    this.availableLocations = [];
                    this.zoneId             = '';
                },

                onZoneChange() {
                    const zone = this.availableZones.find(z => z.id == this.zoneId);
                    this.availableLocations = zone ? zone.locations : [];
                },
            }
        }
    </script>
</x-app-layout>
