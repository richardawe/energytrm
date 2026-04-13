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
