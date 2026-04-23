<x-app-layout>
    <x-slot name="title">New Shipment</x-slot>

    <div class="mb-3">
        <a href="{{ route('operations.shipments.index') }}" class="text-muted small text-decoration-none">← Shipments</a>
    </div>

    <form method="POST" action="{{ route('operations.shipments.store') }}">
        @csrf
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card card-etrm mb-3">
                    <div class="card-header">Trade Link</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Trade <span class="text-danger">*</span></label>
                                <select name="trade_id" class="form-select @error('trade_id') is-invalid @enderror">
                                    <option value="">— select validated trade —</option>
                                    @foreach($trades as $t)
                                        <option value="{{ $t->id }}"
                                            data-incoterm="{{ $t->incoterm_code }}"
                                            data-load="{{ $t->load_port }}"
                                            data-discharge="{{ $t->discharge_port }}"
                                            data-qty="{{ $t->quantity }}"
                                            {{ old('trade_id', $trade?->id) == $t->id ? 'selected' : '' }}>
                                            {{ $t->deal_number }} — {{ $t->counterparty->short_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('trade_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Vessel Name</label>
                                <input type="text" name="vessel_name" class="form-control"
                                       value="{{ old('vessel_name') }}" placeholder="e.g. MV Atlantic Star">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Carrier</label>
                                <select name="carrier_id" class="form-select">
                                    <option value="">— none —</option>
                                    @foreach($carriers as $c)
                                        <option value="{{ $c->id }}" {{ old('carrier_id') == $c->id ? 'selected' : '' }}>
                                            {{ $c->short_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-etrm mb-3">
                    <div class="card-header">Logistics <small class="text-muted fw-normal">(pre-filled from trade)</small></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Incoterm</label>
                                <input type="text" name="incoterm_code" id="incoterm_code" class="form-control"
                                       value="{{ old('incoterm_code', $trade?->incoterm_code) }}" maxlength="10">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Load Port</label>
                                <input type="text" name="load_port" id="load_port" class="form-control"
                                       value="{{ old('load_port', $trade?->load_port) }}">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Discharge Port</label>
                                <input type="text" name="discharge_port" id="discharge_port" class="form-control"
                                       value="{{ old('discharge_port', $trade?->discharge_port) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-etrm mb-3">
                    <div class="card-header">Dates</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">BL Date</label>
                                <input type="date" name="bl_date" class="form-control" value="{{ old('bl_date') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">ETA Load</label>
                                <input type="date" name="eta_load" class="form-control" value="{{ old('eta_load') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">ETA Discharge</label>
                                <input type="date" name="eta_discharge" class="form-control" value="{{ old('eta_discharge') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Actual Load</label>
                                <input type="date" name="actual_load" class="form-control" value="{{ old('actual_load') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Actual Discharge</label>
                                <input type="date" name="actual_discharge" class="form-control" value="{{ old('actual_discharge') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-etrm mb-3">
                    <div class="card-header">Quantities</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Qty Nominated</label>
                                <input type="number" name="qty_nominated" step="0.0001" class="form-control"
                                       value="{{ old('qty_nominated', $trade?->quantity) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Qty Loaded</label>
                                <input type="number" name="qty_loaded" step="0.0001" class="form-control"
                                       value="{{ old('qty_loaded') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Qty Discharged</label>
                                <input type="number" name="qty_discharged" step="0.0001" class="form-control"
                                       value="{{ old('qty_discharged') }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Logistics Details --}}
                <div class="card card-etrm mb-3">
                    <div class="card-header">Logistics Details</div>
                    <div class="card-body">

                        {{-- Laycan --}}
                        <p class="fw-semibold text-muted small mb-2 text-uppercase" style="letter-spacing:.05em;">Laycan &amp; Vessel ETA</p>
                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Laycan Start</label>
                                <input type="date" name="laycan_start" class="form-control" value="{{ old('laycan_start') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Laycan End</label>
                                <input type="date" name="laycan_end" class="form-control" value="{{ old('laycan_end') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Vessel ETA Date</label>
                                <input type="date" name="vessel_eta_date" class="form-control" value="{{ old('vessel_eta_date') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Vessel ETA (text)</label>
                                <input type="text" name="vessel_eta" class="form-control"
                                       value="{{ old('vessel_eta') }}" placeholder="e.g. 15-17 May">
                            </div>
                        </div>

                        {{-- NOR / Laytime --}}
                        <p class="fw-semibold text-muted small mb-2 text-uppercase" style="letter-spacing:.05em;">NOR / Laytime</p>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">NOR Date / Time</label>
                                <input type="datetime-local" name="nor_date" class="form-control"
                                       value="{{ old('nor_date') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Laytime Commencement</label>
                                <input type="datetime-local" name="laytime_commencement" class="form-control"
                                       value="{{ old('laytime_commencement') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Allowed Hours</label>
                                <input type="number" name="allowed_laytime_hours" step="0.01" min="0"
                                       class="form-control" value="{{ old('allowed_laytime_hours') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Time Used (hrs)</label>
                                <input type="number" name="time_used_hours" step="0.01" min="0"
                                       class="form-control" value="{{ old('time_used_hours') }}">
                            </div>
                        </div>

                        {{-- Demurrage --}}
                        <p class="fw-semibold text-muted small mb-2 text-uppercase" style="letter-spacing:.05em;">Demurrage</p>
                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Rate (per day)</label>
                                <input type="number" name="demurrage_rate" step="0.01" min="0"
                                       class="form-control" value="{{ old('demurrage_rate') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Currency</label>
                                <input type="text" name="demurrage_currency" class="form-control"
                                       value="{{ old('demurrage_currency') }}" maxlength="10" placeholder="USD">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Demurrage Amount</label>
                                <input type="number" name="demurrage_amount" step="0.01"
                                       class="form-control" value="{{ old('demurrage_amount') }}"
                                       placeholder="Calculated or override">
                            </div>
                        </div>

                        {{-- Freight --}}
                        <p class="fw-semibold text-muted small mb-2 text-uppercase" style="letter-spacing:.05em;">Freight</p>
                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Freight Cost</label>
                                <input type="number" name="freight_cost" step="0.01" min="0"
                                       class="form-control" value="{{ old('freight_cost') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Freight Basis</label>
                                <select name="freight_basis" class="form-select">
                                    <option value="">— none —</option>
                                    @foreach(['Lump Sum','Per MT','Per BBL','Per MMBTU'] as $fb)
                                        <option value="{{ $fb }}" {{ old('freight_basis') == $fb ? 'selected' : '' }}>{{ $fb }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Draft Survey Quantities --}}
                        <p class="fw-semibold text-muted small mb-2 text-uppercase" style="letter-spacing:.05em;">Survey Quantities</p>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">BL Quantity</label>
                                <input type="number" name="bl_quantity" step="0.0001" min="0"
                                       class="form-control" value="{{ old('bl_quantity') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Draft Survey Qty</label>
                                <input type="number" name="draft_survey_quantity" step="0.0001" min="0"
                                       class="form-control" value="{{ old('draft_survey_quantity') }}">
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card card-etrm mb-3">
                    <div class="card-header">Status</div>
                    <div class="card-body">
                        <label class="form-label fw-semibold">Delivery Status <span class="text-danger">*</span></label>
                        <select name="delivery_status" class="form-select">
                            @foreach(['Scheduled','In Transit','Delivered','Completed','Cancelled'] as $s)
                                <option value="{{ $s }}" {{ old('delivery_status','Scheduled') == $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                        <div class="mt-3">
                            <label class="form-label fw-semibold">Comments</label>
                            <textarea name="comments" class="form-control" rows="3">{{ old('comments') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-primary"
                    style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Create Shipment</button>
            <a href="{{ route('operations.shipments.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>

    <script>
        document.querySelector('[name="trade_id"]').addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            document.getElementById('incoterm_code').value  = opt.dataset.incoterm || '';
            document.getElementById('load_port').value      = opt.dataset.load || '';
            document.getElementById('discharge_port').value = opt.dataset.discharge || '';
            const qtyNom = document.querySelector('[name="qty_nominated"]');
            if (qtyNom && opt.dataset.qty) qtyNom.value = opt.dataset.qty;
        });
    </script>
</x-app-layout>
