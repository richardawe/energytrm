<x-app-layout>
    <x-slot name="title">Edit {{ $shipment->shipment_number }}</x-slot>

    <div class="mb-3">
        <a href="{{ route('operations.shipments.show', $shipment) }}" class="text-muted small text-decoration-none">← {{ $shipment->shipment_number }}</a>
    </div>

    <form method="POST" action="{{ route('operations.shipments.update', $shipment) }}">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card card-etrm mb-3">
                    <div class="card-header">Shipment — {{ $shipment->shipment_number }}</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Vessel Name</label>
                                <input type="text" name="vessel_name" class="form-control"
                                       value="{{ old('vessel_name', $shipment->vessel_name) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Carrier</label>
                                <select name="carrier_id" class="form-select">
                                    <option value="">— none —</option>
                                    @foreach($carriers as $c)
                                        <option value="{{ $c->id }}" {{ old('carrier_id', $shipment->carrier_id) == $c->id ? 'selected' : '' }}>{{ $c->short_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Incoterm</label>
                                <input type="text" name="incoterm_code" class="form-control"
                                       value="{{ old('incoterm_code', $shipment->incoterm_code) }}" maxlength="10">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Load Port</label>
                                <input type="text" name="load_port" class="form-control"
                                       value="{{ old('load_port', $shipment->load_port) }}">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Discharge Port</label>
                                <input type="text" name="discharge_port" class="form-control"
                                       value="{{ old('discharge_port', $shipment->discharge_port) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-etrm mb-3">
                    <div class="card-header">Dates</div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach(['bl_date'=>'BL Date','eta_load'=>'ETA Load','eta_discharge'=>'ETA Discharge','actual_load'=>'Actual Load','actual_discharge'=>'Actual Discharge'] as $field => $label)
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">{{ $label }}</label>
                                <input type="date" name="{{ $field }}" class="form-control"
                                       value="{{ old($field, $shipment->$field?->format('Y-m-d')) }}">
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card card-etrm mb-3">
                    <div class="card-header">Quantities</div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach(['qty_nominated'=>'Qty Nominated','qty_loaded'=>'Qty Loaded','qty_discharged'=>'Qty Discharged'] as $field => $label)
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">{{ $label }}</label>
                                <input type="number" name="{{ $field }}" step="0.0001" class="form-control"
                                       value="{{ old($field, $shipment->$field) }}">
                            </div>
                            @endforeach
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
                                <input type="date" name="laycan_start" class="form-control"
                                       value="{{ old('laycan_start', $shipment->laycan_start?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Laycan End</label>
                                <input type="date" name="laycan_end" class="form-control"
                                       value="{{ old('laycan_end', $shipment->laycan_end?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Vessel ETA Date</label>
                                <input type="date" name="vessel_eta_date" class="form-control"
                                       value="{{ old('vessel_eta_date', $shipment->vessel_eta_date?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Vessel ETA (text)</label>
                                <input type="text" name="vessel_eta" class="form-control"
                                       value="{{ old('vessel_eta', $shipment->vessel_eta) }}" maxlength="50">
                            </div>
                        </div>

                        {{-- NOR / Laytime --}}
                        <p class="fw-semibold text-muted small mb-2 text-uppercase" style="letter-spacing:.05em;">NOR / Laytime</p>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">NOR Date / Time</label>
                                <input type="datetime-local" name="nor_date" class="form-control"
                                       value="{{ old('nor_date', $shipment->nor_date?->format('Y-m-d\TH:i')) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Laytime Commencement</label>
                                <input type="datetime-local" name="laytime_commencement" class="form-control"
                                       value="{{ old('laytime_commencement', $shipment->laytime_commencement?->format('Y-m-d\TH:i')) }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Allowed Hours</label>
                                <input type="number" name="allowed_laytime_hours" step="0.01" min="0"
                                       class="form-control" value="{{ old('allowed_laytime_hours', $shipment->allowed_laytime_hours) }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Time Used (hrs)</label>
                                <input type="number" name="time_used_hours" step="0.01" min="0"
                                       class="form-control" value="{{ old('time_used_hours', $shipment->time_used_hours) }}">
                            </div>
                        </div>

                        @if($shipment->demurrage_or_despatch !== null)
                        <div class="alert alert-info py-2 px-3 mb-3" style="font-size:.85rem;">
                            <strong>Calculated Demurrage / Despatch:</strong>
                            {{ number_format($shipment->demurrage_or_despatch, 2) }} {{ $shipment->demurrage_currency }}
                            {{ $shipment->demurrage_or_despatch >= 0 ? '(Demurrage payable)' : '(Despatch earned)' }}
                        </div>
                        @endif

                        {{-- Demurrage --}}
                        <p class="fw-semibold text-muted small mb-2 text-uppercase" style="letter-spacing:.05em;">Demurrage</p>
                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Rate (per day)</label>
                                <input type="number" name="demurrage_rate" step="0.01" min="0"
                                       class="form-control" value="{{ old('demurrage_rate', $shipment->demurrage_rate) }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Currency</label>
                                <input type="text" name="demurrage_currency" class="form-control"
                                       value="{{ old('demurrage_currency', $shipment->demurrage_currency) }}" maxlength="10">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Demurrage Amount</label>
                                <input type="number" name="demurrage_amount" step="0.01"
                                       class="form-control" value="{{ old('demurrage_amount', $shipment->demurrage_amount) }}">
                            </div>
                        </div>

                        {{-- Freight --}}
                        <p class="fw-semibold text-muted small mb-2 text-uppercase" style="letter-spacing:.05em;">Freight</p>
                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Freight Cost</label>
                                <input type="number" name="freight_cost" step="0.01" min="0"
                                       class="form-control" value="{{ old('freight_cost', $shipment->freight_cost) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Freight Basis</label>
                                <select name="freight_basis" class="form-select">
                                    <option value="">— none —</option>
                                    @foreach(['Lump Sum','Per MT','Per BBL','Per MMBTU'] as $fb)
                                        <option value="{{ $fb }}" {{ old('freight_basis', $shipment->freight_basis) == $fb ? 'selected' : '' }}>{{ $fb }}</option>
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
                                       class="form-control" value="{{ old('bl_quantity', $shipment->bl_quantity) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Draft Survey Qty</label>
                                <input type="number" name="draft_survey_quantity" step="0.0001" min="0"
                                       class="form-control" value="{{ old('draft_survey_quantity', $shipment->draft_survey_quantity) }}">
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card card-etrm mb-3">
                    <div class="card-header">Status</div>
                    <div class="card-body">
                        <label class="form-label fw-semibold">Delivery Status</label>
                        <select name="delivery_status" class="form-select">
                            @foreach(['Scheduled','In Transit','Delivered','Completed','Cancelled'] as $s)
                                <option value="{{ $s }}" {{ old('delivery_status', $shipment->delivery_status) == $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                        <div class="mt-3">
                            <label class="form-label fw-semibold">Comments</label>
                            <textarea name="comments" class="form-control" rows="3">{{ old('comments', $shipment->comments) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-primary"
                    style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">Save Changes</button>
            <a href="{{ route('operations.shipments.show', $shipment) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</x-app-layout>
