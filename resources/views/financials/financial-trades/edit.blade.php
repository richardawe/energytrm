<x-app-layout>
    <x-slot name="title">Amend {{ $trade->deal_number }}</x-slot>

    <div class="mb-3">
        <a href="{{ route('financials.financial-trades.show', $trade) }}" class="text-muted small text-decoration-none">← {{ $trade->deal_number }}</a>
    </div>

    <div class="alert alert-warning py-2 mb-3" role="alert">
        <strong>Amendment:</strong> Saving will issue a new Transaction Number and increment the version.
        @if($trade->trade_status === 'Validated') The trade will revert to <strong>Pending</strong> and require re-validation. @endif
    </div>

    <form method="POST" action="{{ route('financials.financial-trades.update', $trade) }}"
          x-data="financialTradeForm('{{ $trade->instrument_type }}', '{{ old('buy_sell', $trade->buy_sell) }}', '{{ old('swap_type', $trade->swap_type) }}')">
        @csrf
        @method('PUT')

        {{-- Instrument type is fixed on amendment --}}
        <input type="hidden" name="instrument_type" value="{{ $trade->instrument_type }}">

        <div class="card card-etrm mb-3">
            <div class="card-header">Instrument Type</div>
            <div class="card-body">
                <span class="badge fs-6" style="
                    {{ $trade->instrument_type === 'swap'    ? 'background:#0dcaf0;color:#000' : '' }}
                    {{ $trade->instrument_type === 'futures' ? 'background:#ffc107;color:#000' : '' }}
                    {{ $trade->instrument_type === 'options' ? 'background:#6f42c1;color:#fff' : '' }}
                ">{{ ucfirst($trade->instrument_type) }}</span>
                <span class="text-muted small ms-2">Instrument type cannot be changed on amendment</span>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-8">

                {{-- Common --}}
                <div class="card card-etrm mb-3">
                    <div class="card-header">Deal Identity</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Trade Date <span class="text-danger">*</span></label>
                                <input type="date" name="trade_date" class="form-control @error('trade_date') is-invalid @enderror"
                                       value="{{ old('trade_date', $trade->trade_date->format('Y-m-d')) }}">
                                @error('trade_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Buy / Sell <span class="text-danger">*</span></label>
                                <select name="buy_sell" class="form-select @error('buy_sell') is-invalid @enderror" x-model="buySell">
                                    <option value="Buy"  {{ old('buy_sell', $trade->buy_sell) == 'Buy'  ? 'selected' : '' }}>Buy</option>
                                    <option value="Sell" {{ old('buy_sell', $trade->buy_sell) == 'Sell' ? 'selected' : '' }}>Sell</option>
                                </select>
                                @error('buy_sell')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Pay / Receive</label>
                                <input type="text" class="form-control bg-light" readonly
                                       :value="buySell === 'Buy' ? 'Pay' : 'Receive'">
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
                                    <option value="{{ $bu->id }}" {{ old('internal_bu_id', $trade->internal_bu_id) == $bu->id ? 'selected' : '' }}>{{ $bu->short_name }}</option>
                                    @endforeach
                                </select>
                                @error('internal_bu_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Portfolio <span class="text-danger">*</span></label>
                                <select name="portfolio_id" class="form-select @error('portfolio_id') is-invalid @enderror">
                                    <option value="">— select —</option>
                                    @foreach($portfolios as $pf)
                                    <option value="{{ $pf->id }}" {{ old('portfolio_id', $trade->portfolio_id) == $pf->id ? 'selected' : '' }}>{{ $pf->name }}</option>
                                    @endforeach
                                </select>
                                @error('portfolio_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Counterparty <span class="text-danger">*</span></label>
                                <select name="counterparty_id" class="form-select @error('counterparty_id') is-invalid @enderror">
                                    <option value="">— select —</option>
                                    @foreach($counterparties as $cp)
                                    <option value="{{ $cp->id }}" {{ old('counterparty_id', $trade->counterparty_id) == $cp->id ? 'selected' : '' }}>{{ $cp->short_name }}</option>
                                    @endforeach
                                </select>
                                @error('counterparty_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Product <span class="text-danger">*</span></label>
                                <select name="product_id" class="form-select @error('product_id') is-invalid @enderror">
                                    <option value="">— select —</option>
                                    @foreach($products as $p)
                                    <option value="{{ $p->id }}" {{ old('product_id', $trade->product_id) == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                    @endforeach
                                </select>
                                @error('product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Currency <span class="text-danger">*</span></label>
                                <select name="currency_id" class="form-select @error('currency_id') is-invalid @enderror">
                                    <option value="">—</option>
                                    @foreach($currencies as $c)
                                    <option value="{{ $c->id }}" {{ old('currency_id', $trade->currency_id) == $c->id ? 'selected' : '' }}>{{ $c->code }}</option>
                                    @endforeach
                                </select>
                                @error('currency_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Swap fields --}}
                @if($trade->instrument_type === 'swap')
                <div class="card card-etrm mb-3">
                    <div class="card-header">Swap Terms</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Swap Type</label>
                                <select name="swap_type" class="form-select" x-model="swapType">
                                    <option value="commodity" {{ old('swap_type', $trade->swap_type) == 'commodity' ? 'selected' : '' }}>Commodity</option>
                                    <option value="basis"     {{ old('swap_type', $trade->swap_type) == 'basis'     ? 'selected' : '' }}>Basis</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Payment Frequency</label>
                                <select name="payment_frequency" class="form-select">
                                    <option value="Monthly"   {{ old('payment_frequency', $trade->payment_frequency) == 'Monthly'   ? 'selected' : '' }}>Monthly</option>
                                    <option value="Quarterly" {{ old('payment_frequency', $trade->payment_frequency) == 'Quarterly' ? 'selected' : '' }}>Quarterly</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Notional Qty</label>
                                <input type="number" name="notional_quantity" step="0.0001" min="0"
                                       class="form-control" value="{{ old('notional_quantity', $trade->notional_quantity) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">UOM</label>
                                <select name="uom_id" class="form-select">
                                    <option value="">—</option>
                                    @foreach($uoms as $u)
                                    <option value="{{ $u->id }}" {{ old('uom_id', $trade->uom_id) == $u->id ? 'selected' : '' }}>{{ $u->code }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Fixed Rate</label>
                                <input type="number" name="fixed_rate" step="0.000001" min="0"
                                       class="form-control" value="{{ old('fixed_rate', $trade->fixed_rate) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Float Index</label>
                                <select name="float_index_id" class="form-select">
                                    <option value="">— select —</option>
                                    @foreach($indices as $idx)
                                    <option value="{{ $idx->id }}" {{ old('float_index_id', $trade->float_index_id) == $idx->id ? 'selected' : '' }}>{{ $idx->index_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div x-show="swapType === 'basis'" class="col-md-6">
                                <label class="form-label fw-semibold">Second Index (Basis)</label>
                                <select name="second_index_id" class="form-select">
                                    <option value="">— select —</option>
                                    @foreach($indices as $idx)
                                    <option value="{{ $idx->id }}" {{ old('second_index_id', $trade->second_index_id) == $idx->id ? 'selected' : '' }}>{{ $idx->index_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Spread</label>
                                <input type="number" name="spread" step="0.000001"
                                       class="form-control" value="{{ old('spread', $trade->spread) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Start Date</label>
                                <input type="date" name="start_date" class="form-control"
                                       value="{{ old('start_date', $trade->start_date->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">End Date</label>
                                <input type="date" name="end_date" class="form-control"
                                       value="{{ old('end_date', $trade->end_date->format('Y-m-d')) }}">
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Futures fields --}}
                @if($trade->instrument_type === 'futures')
                <div class="card card-etrm mb-3">
                    <div class="card-header">Futures Terms</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Exchange</label>
                                <input type="text" name="exchange" class="form-control" value="{{ old('exchange', $trade->exchange) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Contract Code</label>
                                <input type="text" name="contract_code" class="form-control" value="{{ old('contract_code', $trade->contract_code) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Expiry Date</label>
                                <input type="date" name="expiry_date" class="form-control"
                                       value="{{ old('expiry_date', $trade->expiry_date->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">No. Contracts</label>
                                <input type="number" name="num_contracts" min="1" class="form-control"
                                       value="{{ old('num_contracts', $trade->num_contracts) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Contract Size</label>
                                <input type="number" name="contract_size" step="0.0001" min="0" class="form-control"
                                       value="{{ old('contract_size', $trade->contract_size) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Futures Price</label>
                                <input type="number" name="futures_price" step="0.000001" min="0" class="form-control"
                                       value="{{ old('futures_price', $trade->futures_price) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Margin / Contract</label>
                                <input type="number" name="margin_requirement" step="0.01" min="0" class="form-control"
                                       value="{{ old('margin_requirement', $trade->margin_requirement) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Price Index</label>
                                <select name="futures_index_id" class="form-select">
                                    <option value="">— none —</option>
                                    @foreach($indices as $idx)
                                    <option value="{{ $idx->id }}" {{ old('futures_index_id', $trade->futures_index_id) == $idx->id ? 'selected' : '' }}>{{ $idx->index_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Options fields --}}
                @if($trade->instrument_type === 'options')
                <div class="card card-etrm mb-3">
                    <div class="card-header">Options Terms</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Option Type</label>
                                <select name="option_type" class="form-select">
                                    <option value="call" {{ old('option_type', $trade->option_type) == 'call' ? 'selected' : '' }}>Call</option>
                                    <option value="put"  {{ old('option_type', $trade->option_type) == 'put'  ? 'selected' : '' }}>Put</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Exercise Style</label>
                                <select name="exercise_style" class="form-select">
                                    <option value="European" {{ old('exercise_style', $trade->exercise_style) == 'European' ? 'selected' : '' }}>European</option>
                                    <option value="American" {{ old('exercise_style', $trade->exercise_style) == 'American' ? 'selected' : '' }}>American</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Strike Price</label>
                                <input type="number" name="strike_price" step="0.000001" min="0" class="form-control"
                                       value="{{ old('strike_price', $trade->strike_price) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Expiry Date</label>
                                <input type="date" name="option_expiry_date" class="form-control"
                                       value="{{ old('option_expiry_date', $trade->option_expiry_date->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Premium</label>
                                <input type="number" name="premium" step="0.000001" min="0" class="form-control"
                                       value="{{ old('premium', $trade->premium) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Volatility (decimal)</label>
                                <input type="number" name="volatility" step="0.000001" min="0" max="9.999999" class="form-control"
                                       value="{{ old('volatility', $trade->volatility) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Underlying Index</label>
                                <select name="underlying_index_id" class="form-select">
                                    <option value="">— none —</option>
                                    @foreach($indices as $idx)
                                    <option value="{{ $idx->id }}" {{ old('underlying_index_id', $trade->underlying_index_id) == $idx->id ? 'selected' : '' }}>{{ $idx->index_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

            </div>

            {{-- Right --}}
            <div class="col-lg-4">
                <div class="card card-etrm mb-3">
                    <div class="card-header">Other</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Broker</label>
                                <select name="broker_id" class="form-select">
                                    <option value="">— none —</option>
                                    @foreach($brokers as $b)
                                    <option value="{{ $b->id }}" {{ old('broker_id', $trade->broker_id) == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Agreement</label>
                                <select name="agreement_id" class="form-select">
                                    <option value="">— none —</option>
                                    @foreach($agreements as $ag)
                                    <option value="{{ $ag->id }}" {{ old('agreement_id', $trade->agreement_id) == $ag->id ? 'selected' : '' }}>{{ $ag->name }}</option>
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
            <button type="submit" class="btn btn-primary"
                    style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">
                Save Amendment
            </button>
            <a href="{{ route('financials.financial-trades.show', $trade) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>

    <script>
        function financialTradeForm(instrumentType, buySell, swapType) {
            return { instrumentType, buySell, swapType }
        }
    </script>
</x-app-layout>
