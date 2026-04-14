<x-app-layout>
    <x-slot name="title">New Financial Trade</x-slot>

    <div class="mb-3">
        <a href="{{ route('financials.financial-trades.index') }}" class="text-muted small text-decoration-none">← Financial Trades</a>
    </div>

    <form method="POST" action="{{ route('financials.financial-trades.store') }}"
          x-data="financialTradeForm('{{ old('instrument_type', $instrumentType) }}', '{{ old('buy_sell', '') }}', '{{ old('swap_type', 'commodity') }}')">
        @csrf

        {{-- Instrument Type Selector --}}
        <div class="card card-etrm mb-3">
            <div class="card-header">Instrument Type</div>
            <div class="card-body">
                <div class="d-flex gap-3">
                    @foreach(['swap' => 'Swap', 'futures' => 'Futures', 'options' => 'Options'] as $val => $label)
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="instrument_type"
                               id="type_{{ $val }}" value="{{ $val }}"
                               x-model="instrumentType"
                               {{ old('instrument_type', $instrumentType) === $val ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="type_{{ $val }}">{{ $label }}</label>
                    </div>
                    @endforeach
                </div>
                @error('instrument_type')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="row g-3">
            {{-- Left column --}}
            <div class="col-lg-8">

                {{-- Common: Deal Identity --}}
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
                                <div class="form-text">Auto-derived</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Common: Counterparties --}}
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
                                <label class="form-label fw-semibold">Counterparty <span class="text-danger">*</span></label>
                                <select name="counterparty_id" class="form-select @error('counterparty_id') is-invalid @enderror">
                                    <option value="">— select —</option>
                                    @foreach($counterparties as $cp)
                                    <option value="{{ $cp->id }}" {{ old('counterparty_id') == $cp->id ? 'selected' : '' }}>{{ $cp->short_name }}</option>
                                    @endforeach
                                </select>
                                @error('counterparty_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Product <span class="text-danger">*</span></label>
                                <select name="product_id" class="form-select @error('product_id') is-invalid @enderror">
                                    <option value="">— select —</option>
                                    @foreach($products as $p)
                                    <option value="{{ $p->id }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                    @endforeach
                                </select>
                                @error('product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Currency <span class="text-danger">*</span></label>
                                <select name="currency_id" class="form-select @error('currency_id') is-invalid @enderror">
                                    <option value="">—</option>
                                    @foreach($currencies as $c)
                                    <option value="{{ $c->id }}" {{ old('currency_id') == $c->id ? 'selected' : '' }}>{{ $c->code }}</option>
                                    @endforeach
                                </select>
                                @error('currency_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── SWAP fields ── --}}
                <div x-show="instrumentType === 'swap'" x-cloak>
                    <div class="card card-etrm mb-3">
                        <div class="card-header">Swap Terms</div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Swap Type <span class="text-danger">*</span></label>
                                    <select name="swap_type" class="form-select @error('swap_type') is-invalid @enderror" x-model="swapType">
                                        <option value="commodity" {{ old('swap_type','commodity') == 'commodity' ? 'selected' : '' }}>Commodity</option>
                                        <option value="basis" {{ old('swap_type') == 'basis' ? 'selected' : '' }}>Basis</option>
                                    </select>
                                    @error('swap_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Payment Frequency <span class="text-danger">*</span></label>
                                    <select name="payment_frequency" class="form-select @error('payment_frequency') is-invalid @enderror">
                                        <option value="Monthly"   {{ old('payment_frequency','Monthly') == 'Monthly'   ? 'selected' : '' }}>Monthly</option>
                                        <option value="Quarterly" {{ old('payment_frequency') == 'Quarterly' ? 'selected' : '' }}>Quarterly</option>
                                    </select>
                                    @error('payment_frequency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Notional Qty <span class="text-danger">*</span></label>
                                    <input type="number" name="notional_quantity" step="0.0001" min="0"
                                           class="form-control @error('notional_quantity') is-invalid @enderror"
                                           value="{{ old('notional_quantity') }}">
                                    @error('notional_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">UOM <span class="text-danger">*</span></label>
                                    <select name="uom_id" class="form-select @error('uom_id') is-invalid @enderror">
                                        <option value="">—</option>
                                        @foreach($uoms as $u)
                                        <option value="{{ $u->id }}" {{ old('uom_id') == $u->id ? 'selected' : '' }}>{{ $u->code }}</option>
                                        @endforeach
                                    </select>
                                    @error('uom_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Fixed Rate <span class="text-danger">*</span></label>
                                    <input type="number" name="fixed_rate" step="0.000001" min="0"
                                           class="form-control @error('fixed_rate') is-invalid @enderror"
                                           value="{{ old('fixed_rate') }}">
                                    @error('fixed_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Float Index <span class="text-danger">*</span></label>
                                    <select name="float_index_id" class="form-select @error('float_index_id') is-invalid @enderror">
                                        <option value="">— select index —</option>
                                        @foreach($indices as $idx)
                                        <option value="{{ $idx->id }}" {{ old('float_index_id') == $idx->id ? 'selected' : '' }}>{{ $idx->index_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('float_index_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div x-show="swapType === 'basis'" class="col-md-6">
                                    <label class="form-label fw-semibold">Second Index (Basis) <span class="text-danger">*</span></label>
                                    <select name="second_index_id" class="form-select @error('second_index_id') is-invalid @enderror">
                                        <option value="">— select index —</option>
                                        @foreach($indices as $idx)
                                        <option value="{{ $idx->id }}" {{ old('second_index_id') == $idx->id ? 'selected' : '' }}>{{ $idx->index_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('second_index_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Spread</label>
                                    <input type="number" name="spread" step="0.000001"
                                           class="form-control" value="{{ old('spread', 0) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                                    <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror"
                                           value="{{ old('start_date') }}">
                                    @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">End Date <span class="text-danger">*</span></label>
                                    <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
                                           value="{{ old('end_date') }}">
                                    @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── FUTURES fields ── --}}
                <div x-show="instrumentType === 'futures'" x-cloak>
                    <div class="card card-etrm mb-3">
                        <div class="card-header">Futures Terms</div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Exchange <span class="text-danger">*</span></label>
                                    <input type="text" name="exchange" class="form-control @error('exchange') is-invalid @enderror"
                                           value="{{ old('exchange') }}" placeholder="e.g. ICE, CME">
                                    @error('exchange')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Contract Code <span class="text-danger">*</span></label>
                                    <input type="text" name="contract_code" class="form-control @error('contract_code') is-invalid @enderror"
                                           value="{{ old('contract_code') }}" placeholder="e.g. BRN, WTI">
                                    @error('contract_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Expiry Date <span class="text-danger">*</span></label>
                                    <input type="date" name="expiry_date" class="form-control @error('expiry_date') is-invalid @enderror"
                                           value="{{ old('expiry_date') }}">
                                    @error('expiry_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">No. Contracts <span class="text-danger">*</span></label>
                                    <input type="number" name="num_contracts" min="1"
                                           class="form-control @error('num_contracts') is-invalid @enderror"
                                           value="{{ old('num_contracts') }}">
                                    @error('num_contracts')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Contract Size <span class="text-danger">*</span></label>
                                    <input type="number" name="contract_size" step="0.0001" min="0"
                                           class="form-control @error('contract_size') is-invalid @enderror"
                                           value="{{ old('contract_size') }}">
                                    @error('contract_size')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Futures Price <span class="text-danger">*</span></label>
                                    <input type="number" name="futures_price" step="0.000001" min="0"
                                           class="form-control @error('futures_price') is-invalid @enderror"
                                           value="{{ old('futures_price') }}">
                                    @error('futures_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Margin / Contract</label>
                                    <input type="number" name="margin_requirement" step="0.01" min="0"
                                           class="form-control" value="{{ old('margin_requirement') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Price Index</label>
                                    <select name="futures_index_id" class="form-select">
                                        <option value="">— none —</option>
                                        @foreach($indices as $idx)
                                        <option value="{{ $idx->id }}" {{ old('futures_index_id') == $idx->id ? 'selected' : '' }}>{{ $idx->index_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── OPTIONS fields ── --}}
                <div x-show="instrumentType === 'options'" x-cloak>
                    <div class="card card-etrm mb-3">
                        <div class="card-header">Options Terms</div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Option Type <span class="text-danger">*</span></label>
                                    <select name="option_type" class="form-select @error('option_type') is-invalid @enderror">
                                        <option value="call" {{ old('option_type','call') == 'call' ? 'selected' : '' }}>Call</option>
                                        <option value="put"  {{ old('option_type') == 'put'  ? 'selected' : '' }}>Put</option>
                                    </select>
                                    @error('option_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Exercise Style <span class="text-danger">*</span></label>
                                    <select name="exercise_style" class="form-select @error('exercise_style') is-invalid @enderror">
                                        <option value="European" {{ old('exercise_style','European') == 'European' ? 'selected' : '' }}>European</option>
                                        <option value="American" {{ old('exercise_style') == 'American' ? 'selected' : '' }}>American</option>
                                    </select>
                                    @error('exercise_style')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Strike Price <span class="text-danger">*</span></label>
                                    <input type="number" name="strike_price" step="0.000001" min="0"
                                           class="form-control @error('strike_price') is-invalid @enderror"
                                           value="{{ old('strike_price') }}">
                                    @error('strike_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Expiry Date <span class="text-danger">*</span></label>
                                    <input type="date" name="option_expiry_date" class="form-control @error('option_expiry_date') is-invalid @enderror"
                                           value="{{ old('option_expiry_date') }}">
                                    @error('option_expiry_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Premium <span class="text-danger">*</span></label>
                                    <input type="number" name="premium" step="0.000001" min="0"
                                           class="form-control @error('premium') is-invalid @enderror"
                                           value="{{ old('premium') }}">
                                    @error('premium')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Volatility (decimal)</label>
                                    <input type="number" name="volatility" step="0.000001" min="0" max="9.999999"
                                           class="form-control @error('volatility') is-invalid @enderror"
                                           value="{{ old('volatility') }}" placeholder="e.g. 0.35 = 35%">
                                    @error('volatility')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Underlying Index</label>
                                    <select name="underlying_index_id" class="form-select">
                                        <option value="">— none —</option>
                                        @foreach($indices as $idx)
                                        <option value="{{ $idx->id }}" {{ old('underlying_index_id') == $idx->id ? 'selected' : '' }}>{{ $idx->index_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Right column --}}
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
                                <textarea name="comments" class="form-control" rows="3">{{ old('comments') }}</textarea>
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
            <a href="{{ route('financials.financial-trades.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>

    <script>
        function financialTradeForm(instrumentType, buySell, swapType) {
            return {
                instrumentType,
                buySell,
                swapType,
            }
        }
    </script>
</x-app-layout>
