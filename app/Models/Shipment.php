<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    protected $fillable = [
        'shipment_number', 'trade_id', 'vessel_name', 'carrier_id',
        'incoterm_code', 'load_port', 'discharge_port',
        'bl_date', 'eta_load', 'eta_discharge', 'actual_load', 'actual_discharge',
        'qty_nominated', 'qty_loaded', 'qty_discharged',
        'delivery_status', 'comments', 'created_by',
        // Logistics additions
        'vessel_eta', 'vessel_eta_date',
        'laycan_start', 'laycan_end',
        'nor_date', 'laytime_commencement',
        'allowed_laytime_hours', 'time_used_hours',
        'demurrage_rate', 'demurrage_currency', 'demurrage_amount',
        'freight_cost', 'freight_basis',
        'bl_quantity', 'draft_survey_quantity',
    ];

    protected $casts = [
        'bl_date'                => 'date',
        'eta_load'               => 'date',
        'eta_discharge'          => 'date',
        'actual_load'            => 'date',
        'actual_discharge'       => 'date',
        'qty_nominated'          => 'decimal:4',
        'qty_loaded'             => 'decimal:4',
        'qty_discharged'         => 'decimal:4',
        // Logistics additions
        'vessel_eta_date'        => 'date',
        'laycan_start'           => 'date',
        'laycan_end'             => 'date',
        'nor_date'               => 'datetime',
        'laytime_commencement'   => 'datetime',
        'allowed_laytime_hours'  => 'decimal:2',
        'time_used_hours'        => 'decimal:2',
        'demurrage_rate'         => 'decimal:2',
        'demurrage_amount'       => 'decimal:2',
        'freight_cost'           => 'decimal:2',
        'bl_quantity'            => 'decimal:4',
        'draft_survey_quantity'  => 'decimal:4',
    ];

    public static function nextShipmentNumber(): string
    {
        $year = now()->year;
        $last = static::where('shipment_number', 'like', "SHP-{$year}-%")->max('shipment_number');
        $seq  = $last ? (int) substr($last, -4) + 1 : 1;
        return sprintf('SHP-%d-%04d', $year, $seq);
    }

    /**
     * Calculated demurrage (positive) or despatch (negative).
     * Returns (time_used - allowed) * (rate / 24) or null if data is missing.
     */
    public function getDemurrageOrDespatchAttribute(): ?float
    {
        if ($this->time_used_hours === null || $this->allowed_laytime_hours === null || $this->demurrage_rate === null) {
            return null;
        }
        return ((float) $this->time_used_hours - (float) $this->allowed_laytime_hours)
            * ((float) $this->demurrage_rate / 24);
    }

    public function trade(): BelongsTo    { return $this->belongsTo(Trade::class); }
    public function carrier(): BelongsTo  { return $this->belongsTo(Party::class, 'carrier_id'); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
